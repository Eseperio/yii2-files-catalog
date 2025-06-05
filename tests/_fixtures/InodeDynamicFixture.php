<?php

namespace tests\_fixtures;

use yii\test\ActiveFixture;
use Ramsey\Uuid\Uuid;
use eseperio\filescatalog\models\Inode;

class InodeDynamicFixture extends ActiveFixture
{
    // Tipos de nodos: 1 = archivo, 2 = directorio
    const TYPE_FILE = 1;
    const TYPE_DIRECTORY = 2;
    
    // IDs de los inodos del fixture fijo
    const ROOT_ID = 1;
    const DIR_ID = 2;
    const FILE_ID = 3;
    
    // Contador para generar IDs únicos, comenzando después de los IDs fijos
    private $counter = 4;
    
    // Modelo a utilizar
    public $modelClass = 'eseperio\filescatalog\models\Inode';
    
    // Dependencia del fixture fijo
    public $depends = ['tests\_fixtures\InodeFixture'];

    /**
     * Generar los datos dinámicamente en lugar de cargarlos desde un archivo
     */
    public function getData()
    {
        $data = [];
        
        // No creamos raíz, utilizamos la existente del fixture fijo
        $rootId = self::ROOT_ID;
        
        // Crear las 4 carpetas principales bajo root
        $emptyDir = $this->createInode('empty_dir', $rootId, self::TYPE_DIRECTORY, 1);
        $data['empty_dir'] = $emptyDir;
        
        $threeFoldersDir = $this->createInode('three_folders_dir', $rootId, self::TYPE_DIRECTORY, 1);
        $data['three_folders_dir'] = $threeFoldersDir;
        
        $nestedFoldersDir = $this->createInode('nested_folders_dir', $rootId, self::TYPE_DIRECTORY, 1);
        $data['nested_folders_dir'] = $nestedFoldersDir;
        
        $mixedContentDir = $this->createInode('mixed_content_dir', $rootId, self::TYPE_DIRECTORY, 1);
        $data['mixed_content_dir'] = $mixedContentDir;
        
        // Crear estructura para carpeta con 3 subcarpetas
        $this->createThreeFoldersStructure($threeFoldersDir, $data);
        
        // Crear estructura para carpeta con carpetas anidadas
        $this->createNestedFoldersStructure($nestedFoldersDir, $data);
        
        // Crear estructura para carpeta con contenido mixto
        $this->createMixedContentStructure($mixedContentDir, $data);
        
        return $data;
    }
    
    /**
     * Crear un único registro de inodo
     */
    private function createInode($name, $parentId, $type, $depth)
    {
        $id = $this->counter++;
        
        return [
            "id" => $id,
            "uuid" => Uuid::uuid4()->toString(),
            "name" => $name,
            "extension" => null,
            "mime" => null,
            "type" => $type,
            "parent_id" => $parentId,
            "md5hash" => null,
            "depth" => $depth,
            "filesize" => 0,
            "created_at" => 1694611480,
            "updated_at" => null,
            "created_by" => null,
            "updated_by" => null,
            "author_name" => "System",
            "editor_name" => null
        ];
    }
    
    /**
     * Crear estructura de carpeta con 3 subcarpetas, dos ficheros en cada una
     */
    private function createThreeFoldersStructure($parent, &$data)
    {
        // Crear 3 subcarpetas
        for ($i = 1; $i <= 3; $i++) {
            $folder = $this->createInode("subfolder_{$i}", $parent['id'], self::TYPE_DIRECTORY, $parent['depth'] + 1);
            $data["subfolder_{$i}"] = $folder;
            
            // Dos ficheros en cada subcarpeta
            for ($j = 1; $j <= 2; $j++) {
                $file = $this->createInode("file_{$i}_{$j}.txt", $folder['id'], self::TYPE_FILE, $folder['depth'] + 1);
                $file['extension'] = 'txt';
                $file['mime'] = 'text/plain';
                $data["file_{$i}_{$j}"] = $file;
            }
        }
    }
    
    /**
     * Crear estructura de carpeta con dos carpetas, y a su vez otras dos carpetas dentro de cada una,
     * y un fichero en cada una de las subcarpetas
     */
    private function createNestedFoldersStructure($parent, &$data)
    {
        // Crear 2 carpetas principales
        for ($i = 1; $i <= 2; $i++) {
            $folder1 = $this->createInode("main_folder_{$i}", $parent['id'], self::TYPE_DIRECTORY, $parent['depth'] + 1);
            $data["main_folder_{$i}"] = $folder1;
            
            // Crear 2 subcarpetas dentro de cada carpeta principal
            for ($j = 1; $j <= 2; $j++) {
                $folder2 = $this->createInode("sub_folder_{$i}_{$j}", $folder1['id'], self::TYPE_DIRECTORY, $folder1['depth'] + 1);
                $data["sub_folder_{$i}_{$j}"] = $folder2;
                
                // Un fichero en cada subcarpeta
                $file = $this->createInode("nested_file_{$i}_{$j}.doc", $folder2['id'], self::TYPE_FILE, $folder2['depth'] + 1);
                $file['extension'] = 'doc';
                $file['mime'] = 'application/msword';
                $data["nested_file_{$i}_{$j}"] = $file;
            }
        }
    }
    
    /**
     * Crear estructura de carpeta con un fichero y una carpeta dentro,
     * y dentro de la carpeta otra carpeta con un fichero
     */
    private function createMixedContentStructure($parent, &$data)
    {
        // Crear un fichero directamente bajo el directorio padre
        $file1 = $this->createInode("root_file.pdf", $parent['id'], self::TYPE_FILE, $parent['depth'] + 1);
        $file1['extension'] = 'pdf';
        $file1['mime'] = 'application/pdf';
        $data["root_file"] = $file1;
        
        // Crear una carpeta bajo el directorio padre
        $folder1 = $this->createInode("mixed_subfolder", $parent['id'], self::TYPE_DIRECTORY, $parent['depth'] + 1);
        $data["mixed_subfolder"] = $folder1;
        
        // Crear otra carpeta dentro de la anterior
        $folder2 = $this->createInode("mixed_nested_folder", $folder1['id'], self::TYPE_DIRECTORY, $folder1['depth'] + 1);
        $data["mixed_nested_folder"] = $folder2;
        
        // Crear un fichero dentro de la carpeta más anidada
        $file2 = $this->createInode("nested_doc.xlsx", $folder2['id'], self::TYPE_FILE, $folder2['depth'] + 1);
        $file2['extension'] = 'xlsx';
        $file2['mime'] = 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet';
        $data["nested_doc"] = $file2;
    }
}
