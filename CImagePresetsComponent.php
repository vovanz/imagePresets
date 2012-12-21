<?php

/**
 * Presets for Kohana Image extension
 * 
 * @author VovanZ
 */
class CImagePresetsComponent extends CApplicationComponent {

    /**
	 *	Enable profiling 
	 *
	 * @var boolean
	 */
	public $profile = false;

    /**
     * Path to files folder. Necessary for using "img" function.
     * 
     * @var string
     */
    public $files_path = '';

    /**
     * Array of presets.
     * 
     * Structure:
     * 'presets' => array(
     *      '<preset name>' => array(
     *          '<what to do with image>' => <array of parameters>,
     *          ...
     *      ),
     *      ...
     * ),
     * 
     * Example:
     * 'presets' => array(
     *     'project' => array(
     *          'crop' => array(570, 210),
     *          'resize' => array(570, 210, 1),
     *      ),
     *      'preview' => array(
     *          'resize' => array(0, 250, 3),
     *      ),
     * ),
     * 
     * @var array
     */
    public $presets;
    
    /**
     * Name of the image component.
     * 
     * @var string 
     */
    public $image_component = 'image';

    
    /**
     * Main function.
     * 
     * @param string $full_path Full path to the original file. Полный путь к исходному файлу.
     * @param string $cache_folder Full path to the folder where processed file will be saved. Папка, куда будет сохранен преобразованный файл.
     * @param string $preset_name Name of preset.
     * @param boolean $force If false will not replace processed file. Заменять ли файл, если он уже существует.
     * @return void
     */
     
     
    private function crop_resize($image, $params) {
    	$w=$params[0];
		$h=$params[1];
		if($w/$h > $image->width/$image->height) {
			$image->resize($w, NULL);
			$image->crop($w, $h);			
		} else {
			$image->resize(NULL, $h);
			$image->crop($w, $h);	
		}
    	return $image;
    }
     
    public function image($full_path, $cache_folder, $preset_name, $force = false) {
        $full_path_info = pathinfo($full_path);
        $fpath = $full_path_info['basename'];

        $image_component = $this->image_component;
		
        $image = Yii::app()->$image_component->load($full_path);
        if (!file_exists($cache_folder . $fpath) || $force) {
            foreach ($this->presets[$preset_name] as $action => $params) {
            	if($this->profile) {
            		Yii::beginProfile('ImagePresets: '.$action.'ing'.' image '.$fpath);
            	}
                switch ($action) {
					case 'crop_resize':
						$image=$this->crop_resize($image, $params);
						break;
                    case 'crop':
                        $image->crop($params[0], $params[1], $params[2], $params[3]);
                        break;
                    case 'resize':
                        $image->resize($params[0], $params[1], $params[2]);
                        break;
                    default:
                        $image->$action($params[0]);
                        break;
                }
				if($this->profile) {
            		Yii::endProfile('ImagePresets: '.$action.'ing'.' image '.$fpath);
            	}
            }
            $image->save($cache_folder . DIRECTORY_SEPARATOR . $fpath);
        }
    }

    /**
     * Alias to "image" function. Searches the original file in $files_path folder (if set). If $files_path is set to false uses global files_path.
     * Saves the processed file into images/<preset name> under $files_path.
     * 
     * Алиас для функции "image". Берёт исходный файл из папки указанной в $files_path (по умолчанию, значение берется из конфига).
     * Кладёт преобразованный файл в папку images/<preset name>, создавая её в папке, указанной в $files_name
     * 
     * @param string $fpath Relative path to the original file.
     * @param string $preset_name Name of preset.
     * @param string $files_path Folder of the original file.
     * @return string URL of the processed file.
     */
    
    public function img($fpath, $preset_name, $files_path = false) {
    	if($this->profile) {
    		Yii::beginProfile('ImagePresets: '.'working on image '.$fpath.' for '.$preset_name);
    	}
        if (!$files_path)
            $files_path = $this->files_path;
		
        if (!file_exists(Yii::app()->basePath . DIRECTORY_SEPARATOR . '..' . $files_path . 'images' . DIRECTORY_SEPARATOR . $preset_name . DIRECTORY_SEPARATOR . $fpath)) {
            if (!file_exists(Yii::app()->basePath . DIRECTORY_SEPARATOR . '..' . $files_path . 'images'))
                mkdir(Yii::app()->basePath . DIRECTORY_SEPARATOR . '..' . $files_path . 'images', 0777, true);
            if (!file_exists(Yii::app()->basePath . DIRECTORY_SEPARATOR . '..' . $files_path . 'images' . DIRECTORY_SEPARATOR . $preset_name))
                mkdir(Yii::app()->basePath . DIRECTORY_SEPARATOR . '..' . $files_path . 'images' . DIRECTORY_SEPARATOR . $preset_name, 0777, true);

            $full_path = Yii::app()->basePath . DIRECTORY_SEPARATOR . '..' . $files_path . $fpath;
            $cache_folder = Yii::app()->basePath . DIRECTORY_SEPARATOR . '..' . $files_path . 'images' . DIRECTORY_SEPARATOR . $preset_name;

            $this->image($full_path, $cache_folder, $preset_name);
        }
    	if($this->profile) {
    		Yii::endProfile('ImagePresets: '.'working on image '.$fpath.' for '.$preset_name);
    	}
        return $files_path . 'images/' . $preset_name . '/' . $fpath;
    }

}
