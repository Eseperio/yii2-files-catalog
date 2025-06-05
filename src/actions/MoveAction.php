<?php
/**
 *
 * Developed by Waizabú <code@waizabu.com>
 *
 *
 */

namespace eseperio\filescatalog\actions;


use yii\helpers\Html;
use eseperio\filescatalog\controllers\DefaultController;
use eseperio\filescatalog\dictionaries\InodeTypes;
use eseperio\filescatalog\helpers\AclHelper;
use eseperio\filescatalog\models\Inode;
use eseperio\filescatalog\traits\ModuleAwareTrait;
use Yii;
use yii\base\Action;
use yii\base\DynamicModel;
use yii\web\ForbiddenHttpException;
use yii\web\NotFoundHttpException;

/**
 * Class MoveAction
 * @property DefaultController $controller
 * @package eseperio\filescatalog\actions
 */
class MoveAction extends Action
{
    use ModuleAwareTrait;

    /**
     * @var Inode La instancia del inodo a mover
     */
    private $_model;

    /**
     * @var DynamicModel El modelo del formulario para seleccionar destino
     */
    private $_moveFormModel;

    /**
     * @param string $uuid UUID del inodo a mover
     * @return mixed
     * @throws ForbiddenHttpException
     * @throws NotFoundHttpException
     * @throws \Throwable
     */
    public function run($uuid)
    {
        // Validamos los permisos y obtenemos el modelo
        $this->validateAndLoadModel($uuid);
        
        // Creamos el modelo de formulario
        $this->_moveFormModel = $this->createMoveFormModel();

        $trans = Yii::$app->db->beginTransaction();

        try {
            // Si se envió el formulario, procesamos los datos
            if ($this->_moveFormModel->load(Yii::$app->request->post()) && $this->_moveFormModel->validate()) {
                if ($this->processMove($trans)) {
                    return $this->controller->goBack([
                        'index', 
                        'uuid' => $this->getDestinationFolder()->uuid
                    ]);
                }
            }
        } catch (\Throwable $e) {
            $trans->rollBack();
            throw $e;
        }

        // Renderizamos el formulario
        return $this->controller->render('move', [
            'model' => $this->_model,
            'moveFormModel' => $this->_moveFormModel
        ]);
    }

    /**
     * Validates the move operation and loads the model
     * 
     * @param string $uuid UUID del inodo
     * @throws NotFoundHttpException Si el módulo no permite mover o el inodo no existe
     * @throws ForbiddenHttpException Si el usuario no tiene permisos
     */
    private function validateAndLoadModel($uuid)
    {
        // Check if moving is allowed in the module configuration
        if (!$this->module->allowMoving) {
            throw new NotFoundHttpException();
        }

        // Get the UUID of the inode to move
        $this->_model = $this->controller->findModel($uuid, Yii::$app->request->get('created_at'));

        if (empty($this->_model)) {
            throw new NotFoundHttpException('Page not found');
        }

        // Check if the user has write permissions for the inode
        if (!AclHelper::canWrite($this->_model)) {
            throw new ForbiddenHttpException(Yii::t('filescatalog', 'You can not move this item'));
        }
    }

    /**
     * Crea el modelo de formulario para seleccionar carpeta destino
     * 
     * @return DynamicModel
     */
    private function createMoveFormModel()
    {
        $moveFormModel = new DynamicModel([
            'destination_folder'
        ]);
        $moveFormModel->addRule('destination_folder', 'required');
        $moveFormModel->addRule('destination_folder', 'string');

        return $moveFormModel;
    }

    /**
     * Obtiene la carpeta destino a partir del UUID proporcionado
     * 
     * @return Inode|null
     * @throws ForbiddenHttpException
     */
    private function getDestinationFolder()
    {
        $destinationUuid = $this->_moveFormModel->destination_folder;
        $destinationFolder = $this->controller->findModel($destinationUuid);
        
        // Check if the destination is a folder
        if ($destinationFolder->type !== InodeTypes::TYPE_DIR) {
            throw new ForbiddenHttpException(Yii::t('filescatalog', 'Destination must be a folder'));
        }
        
        // Check if the user has write permissions for the destination folder
        if (!AclHelper::canWrite($destinationFolder)) {
            throw new ForbiddenHttpException(Yii::t('filescatalog', 'You do not have permission to write to the destination folder'));
        }

        return $destinationFolder;
    }

    /**
     * Validates the destination folder for the move operation
     *
     * @param Inode $destinationFolder La carpeta destino
     * @return bool True si el destino es válido
     * @throws \yii\web\ForbiddenHttpException
     */
    private function validateDestination($destinationFolder)
    {
        // Check if the destination folder is not the current folder
        if ($this->_model->parent_id === $destinationFolder->id) {
            $this->_moveFormModel->addError('destination_folder', Yii::t('filescatalog', 'The item is already in this folder'));
            return false;
        }
        
        // Check if the destination folder is not a child of the item being moved (if it's a folder)
        if ($this->_model->type === InodeTypes::TYPE_DIR) {
            $childrenIds = $this->_model->getDescendantsIds(null, true);
            if (in_array($destinationFolder->id, $childrenIds)) {
                throw new ForbiddenHttpException(Yii::t('filescatalog', 'Cannot move a folder into its own subfolder'));
            }
        }
        
        // Check if there's already an item with the same name in the destination folder
        $existingItem = $this->checkForNameCollisions($destinationFolder);
        if ($existingItem) {
            $this->_moveFormModel->addError('destination_folder', Yii::t('filescatalog', 'An item with the same name already exists in the destination folder'));
            return false;
        }

        return true;
    }

    /**
     * Comprueba si existe un elemento con el mismo nombre en la carpeta destino
     *
     * @param Inode $destinationFolder La carpeta destino
     * @return Inode|null El elemento duplicado si existe
     * @throws \yii\base\InvalidConfigException
     */
    private function checkForNameCollisions($destinationFolder)
    {
        return Inode::find()
            ->where([
                'parent_id' => $destinationFolder->id,
                'name' => $this->_model->name,
                'type' => $this->_model->type
            ])
            ->one();
    }

    /**
     * Recopila todos los inodos relacionados que deben moverse junto con el inodo principal
     *
     * @return array Lista de inodos a mover
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\base\UserException
     */
    private function collectRelatedInodes()
    {
        $inodesToMove = [$this->_model];
        
        // Si el inodo es un archivo, recolectamos también sus versiones
        if ($this->_model->type === InodeTypes::TYPE_FILE) {
            // Get all versions of this file
            $versions = $this->_model->getVersions()->all();
            $inodesToMove = array_merge($inodesToMove, $versions);
        } 
        // Si el inodo es una versión, recolectamos el archivo original y otras versiones
        elseif ($this->_model->type === InodeTypes::TYPE_VERSION) {
            // Get the original file
            $original = $this->_model->getOriginal()->one();
            if ($original) {
                // Add original to the list of inodes to move
                $inodesToMove[] = $original;
                
                // Get all other versions of the original file
                $otherVersions = $original->getVersions()
                    ->andWhere(['not', ['id' => $this->_model->id]])
                    ->all();
                $inodesToMove = array_merge($inodesToMove, $otherVersions);
            }
        }

        return $inodesToMove;
    }

    /**
     * Runs the move operation within a transaction
     * @param yii\db\Transaction $trans La transacción abierta
     * @return bool True si el movimiento se completó con éxito
     * @throws \Throwable
     * @throws \yii\base\InvalidConfigException
     * @throws \yii\base\UserException
     * @throws \yii\db\Exception
     * @throws \yii\web\ForbiddenHttpException
     */
    private function processMove($trans)
    {
        try {
            $destinationFolder = $this->getDestinationFolder();
            
            // Validar que el destino sea adecuado
            if (!$this->validateDestination($destinationFolder)) {
                return false;
            }
            
            // Recopilar todos los inodos relacionados que deben moverse juntos
            $inodesToMove = $this->collectRelatedInodes();
            
            // Actualizar el parent_id para todos los inodos relacionados
            $this->moveInodes($inodesToMove, $destinationFolder->id);
            
            $trans->commit();
            return true;
            
        } catch (\Throwable $e) {
            $trans->rollBack();
            throw $e;
        }
    }

    /**
     * Update the parent_id of all inodes in the list
     * 
     * @param array $inodes Lista de inodos a mover
     * @param int $destinationId ID de la carpeta destino
     */
    private function moveInodes($inodes, $destinationId)
    {
        foreach ($inodes as $inode) {
            $inode->updateAttributes(['parent_id' => $destinationId]);
        }
    }
}
