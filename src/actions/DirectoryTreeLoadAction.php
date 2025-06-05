<?php

namespace eseperio\filescatalog\actions;

use eseperio\filescatalog\dictionaries\InodeTypes;
use eseperio\filescatalog\models\Inode;
use eseperio\filescatalog\models\InodeQuery;
use eseperio\filescatalog\services\InodeHelper;
use Yii;
use yii\base\Action;
use yii\web\Response;
use yii\web\NotFoundHttpException;
use eseperio\filescatalog\exceptions\FilexAccessDeniedException;

/**
 * DirectoryTreeLoadAction handles AJAX requests to load directory contents for the DirectoryTreeWidget.
 * Works with virtual filesystem based on inodes instead of real directories.
 */
class DirectoryTreeLoadAction extends Action
{
    /**
     * @var callable A callback to filter directories and files
     */
    public $filter;
    
    /**
     * @inheritdoc
     */
    public function init()
    {
        parent::init();
    }
    
    /**
     * Run the action
     * 
     * @return array The directory contents as JSON
     */
    public function run()
    {
        Yii::$app->response->format = Response::FORMAT_JSON;
        
        $uuid = Yii::$app->request->get('uuid', null);
        $mode = (int)Yii::$app->request->get('mode', 2); // Default to MODE_ALL
        $extensions = Yii::$app->request->get('extensions', []);
        $rootNodeUuid = Yii::$app->request->get('rootNodeUuid', null); // Obtener el UUID del nodo raíz
        $excludedUuids = Yii::$app->request->get('excludedUuids', []); // Obtener los UUIDs excluidos
        
        try {
            // Get the current directory inode
            $directory = InodeHelper::getModel($uuid);
            
            // Make sure this is actually a directory
            if ($directory->type !== InodeTypes::TYPE_DIR) {
                return [
                    'success' => false,
                    'message' => 'Not a directory',
                    'items' => []
                ];
            }
            
            // Get directory contents
            $query = $directory->getChildren()
                ->with(['accessControlList'])
                ->excludeVersions()
                ->withSymlinksReferences()
                ->onlyReadable();
            
            if ($mode === 1) { // MODE_DIRECTORIES_ONLY
                $query->onlyDirs();
            }
            
            // Apply extension filter if needed
            if ($mode === 2 && !empty($extensions)) { // MODE_ALL and we have extensions
                $query->andWhere(['or', 
                    [InodeQuery::prefix('type') => InodeTypes::TYPE_DIR],
                    ['and', 
                        [InodeQuery::prefix('type') => InodeTypes::TYPE_FILE],
                        [InodeQuery::prefix('extension') => $extensions]
                    ]
                ]);
            }
            
            // Exclude the root node if provided
            if (!empty($rootNodeUuid)) {
                $rootNode = InodeHelper::getModel($rootNodeUuid);
                if ($rootNode) {
                    $query->andWhere(['!=', InodeQuery::prefix('id'), $rootNode->id]);
                }
            }
            
            // Excluir los UUIDs especificados
            if (!empty($excludedUuids)) {
                $query->andWhere(['NOT IN', InodeQuery::prefix('uuid'), $excludedUuids]);
            }
            
            // Order the results
            $query->orderByType()->orderAZ();
            
            // Get the results
            $inodes = $query->all();
            
            // Format the results
            $items = [];
            foreach ($inodes as $inode) {
                $items[] = [
                    'name' => $inode->name,
                    'uuid' => $inode->uuid,
                    'isDir' => $inode->type === InodeTypes::TYPE_DIR,
                    'extension' => $inode->extension,
                    'type' => $inode->type
                ];
            }
            
            // Apply custom filter if provided
            if (is_callable($this->filter)) {
                $items = array_filter($items, $this->filter);
            }
            
            return [
                'success' => true,
                'items' => $items
            ];
        } catch (NotFoundHttpException $e) {
            return [
                'success' => false,
                'message' => 'Directory not found',
                'items' => []
            ];
        } catch (FilexAccessDeniedException $e) {
            return [
                'success' => false,
                'message' => 'Access denied',
                'items' => []
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
                'items' => []
            ];
        }
    }
}
