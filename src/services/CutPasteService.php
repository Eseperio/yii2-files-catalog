<?php
/**
 *
 * Developed by Waizabú <code@waizabu.com>
 *
 *
 */

namespace eseperio\filescatalog\services;

use eseperio\filescatalog\dictionaries\InodeTypes;
use eseperio\filescatalog\helpers\AclHelper;
use eseperio\filescatalog\models\Inode;
use eseperio\filescatalog\traits\ModuleAwareTrait;
use Yii;
use yii\base\Component;
use yii\web\ForbiddenHttpException;

/**
 * Class CutPasteService
 * Service for handling cut and paste operations on inodes
 * @package eseperio\filescatalog\services
 */
class CutPasteService extends Component
{
    use ModuleAwareTrait;

    /**
     * @var string Session key to store cut inodes UUIDs
     */
    private $sessionKey = 'filex_cut_inodes';

    /**
     * Cut a single inode and store its UUID in the session
     * 
     * @param Inode $inode The inode to cut
     * @return bool Whether the operation was successful
     * @throws ForbiddenHttpException If the user doesn't have write permissions
     */
    public function cutInode(Inode $inode): bool
    {
        if (!$this->module->allowCutPaste) {
            throw new ForbiddenHttpException(Yii::t('filescatalog', 'Cut and paste is not allowed'));
        }

        // Check if user has write permissions for the inode
        if (!AclHelper::canWrite($inode)) {
            throw new ForbiddenHttpException(Yii::t('filescatalog', 'You do not have write permissions for this item'));
        }

        try {
            // Store the UUID in the session
            Yii::$app->session->set($this->sessionKey, [$inode->uuid]);
            return true;
        } catch (\Throwable $e) {
            Yii::error('Error cutting item: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Cut multiple inodes and store their UUIDs in the session
     * 
     * @param Inode[] $inodes The inodes to cut
     * @return bool Whether the operation was successful
     */
    public function cutInodes(array $inodes): bool
    {
        if (empty($inodes)) {
            return false;
        }

        try {
            $uuids = [];
            foreach ($inodes as $inode) {
                // Only include inodes that the user has write permissions for
                if (AclHelper::canWrite($inode)) {
                    $uuids[] = $inode->uuid;
                }
            }

            if (empty($uuids)) {
                return false;
            }

            // Store the UUIDs in the session
            Yii::$app->session->set($this->sessionKey, $uuids);
            return true;
        } catch (\Throwable $e) {
            Yii::error('Error cutting items: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Get the UUIDs of cut inodes from the session
     * 
     * @return array The UUIDs of cut inodes
     */
    public function getCutInodeUuids(): array
    {
        return Yii::$app->session->get($this->sessionKey, []);
    }

    /**
     * Get the cut inodes from the session
     * 
     * @return Inode[] The cut inodes
     */
    public function getCutInodes(): array
    {
        $cutUuids = $this->getCutInodeUuids();
        if (empty($cutUuids)) {
            return [];
        }

        return Inode::find()->where(['uuid' => $cutUuids])->all();
    }

    /**
     * Clear cut inodes from the session
     */
    public function clearCutInodes(): void
    {
        Yii::$app->session->remove($this->sessionKey);
    }

    /**
     * Paste cut inodes to the destination directory
     * 
     * @param Inode $destination The destination directory
     * @return bool Whether the operation was successful
     * @throws ForbiddenHttpException If the destination is not a directory or the user doesn't have write permissions
     */
    public function pasteInodes(Inode $destination): bool
    {
        if ($destination->type !== InodeTypes::TYPE_DIR) {
            throw new ForbiddenHttpException(Yii::t('filescatalog', 'Destination must be a folder'));
        }

        // Check if user has write permissions for the destination
        if (!AclHelper::canWrite($destination)) {
            throw new ForbiddenHttpException(Yii::t('filescatalog', 'You do not have permission to write to the destination folder'));
        }

        $cutInodes = $this->getCutInodes();
        if (empty($cutInodes)) {
            return false;
        }

        $transaction = Yii::$app->db->beginTransaction();

        try {
            foreach ($cutInodes as $inode) {
                // Skip if user doesn't have write permissions for the inode
                if (!AclHelper::canWrite($inode)) {
                    continue;
                }

                // Check if the destination folder is not a child of the item being moved (if it's a folder)
                if ($inode->type === InodeTypes::TYPE_DIR) {
                    $childrenIds = $inode->getDescendantsIds(null, true);
                    if (in_array($destination->id, $childrenIds)) {
                        throw new ForbiddenHttpException(Yii::t('filescatalog', 'Cannot move a folder into its own subfolder'));
                    }
                }

                // Check for name collisions
                $existingItem = Inode::find()
                    ->where([
                        'parent_id' => $destination->id,
                        'name' => $inode->name,
                        'type' => $inode->type
                    ])
                    ->one();

                if ($existingItem) {
                    Yii::$app->session->setFlash('error', Yii::t('filescatalog', 'An item with the name "{name}" already exists in the destination folder', [
                        'name' => $inode->name
                    ]));
                    $transaction->rollBack();
                    return false;
                }

                // If it's a file, also move its versions
                $inodesToMove = [$inode];
                if ($inode->type === InodeTypes::TYPE_FILE) {
                    $versions = $inode->getVersions()->all();
                    $inodesToMove = array_merge($inodesToMove, $versions);
                }

                // Update parent_id for all inodes
                foreach ($inodesToMove as $inodeToMove) {
                    $inodeToMove->updateAttributes(['parent_id' => $destination->id]);
                }
            }

            $transaction->commit();
            
            // Clear cut inodes from session after successful paste
            $this->clearCutInodes();
            
            return true;
        } catch (\Throwable $e) {
            $transaction->rollBack();
            Yii::error('Error pasting items: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Check if there are any cut inodes in the session
     * 
     * @return bool Whether there are any cut inodes
     */
    public function hasCutInodes(): bool
    {
        return !empty($this->getCutInodeUuids());
    }
}
