imagePresets
============

Presets for Kohana Image extension (Yii)

Usage:
- place imagePresets folder into extentions folder
- configuration. Add to config:

	    'components' => array(
	    	//.......................
			'imagePresets' => array(
		            'class' => 'application.extensions.imagePresets.CImagePresetsComponent',
		            'presets' => array(
		             	//see 'presets' property
		            ),
					'files_path' => '', //see 'files_path' property
				),
			//....................
			),
			