<?php
/**
 * Contact Model
 * @author James Fairhurst <info@jamesfairhurst.co.uk>
 */
class Contact extends AppModel {
	/**
	 * Model Class name
	 * @param string
	 */

	public $name = 'Contact';
	/**
	 * Validation Rules
	 * @param array
	 */
	public $validate = array(
		'name' => 'notBlank',

		'filename' => array(
			// http://book.cakephp.org/2.0/en/models/data-validation.html#Validation::uploadError
			'uploadError' => array(
				'rule' => 'uploadError',
				'message' => 'Something went wrong with the file upload',
				'required' => TRUE, //FALSE
				'allowEmpty' => FALSE, //TRUE
			),

			// http://book.cakephp.org/2.0/en/models/data-validation.html#Validation::mimeType
			'mimeType' => array(
//				'rule' => array('mimeType', array('video/mp4','video/x-flv','video/webm','video/m4v','video/ogg','video/3gpp','video/3gpp2','video/x-matroska','video/quicktime','video/theora','video/x-msvideo','video/mpeg')),
				'rule' => array('extension', array('mp4', 'flv', 'webm', 'm4v','mkv','mov','avi','ogv','3gp')),
				'message' => 'Invalid video file type. Accepted types: mp4, flv, webm, m4v, mkv, mov, avi, ogv, 3gp', 
				// .ogv-NA		mkv-NA		3gp-A,C,ND		mov-A, NC,			
				'required' => TRUE,
				'allowEmpty' => TRUE, 
			),

			// custom callback to deal with the file upload
			'processUpload' => array(
				'rule' => 'processUpload',
				'message' => 'Something went wrong processing your file',
				'required' => FALSE,
				'allowEmpty' => TRUE,
				'last' => TRUE,
			)
		)

	);

//ft, tt, ftt

	/**
	 * Upload Directory relative to WWW_ROOT
	 * @param string
	 */
	public $uploadDir = 'uploads';
	/**
	 * Before Validation Callback
	 * @param array $options
	 * @return boolean
	 */
	public function beforeValidate($options = array()) {
print_r($this->data);
//print_r($this->data[$this->alias]['filename']);
		// ignore empty file - causes issues with form validation when file is empty and optional
		if (!empty($this->data[$this->alias]['filename']['error']) && $this->data[$this->alias]['filename']['error']==4 && $this->data[$this->alias]['filename']['size']==0) {
//print_r("\n$this->data[$this->alias]");
			unset($this->data[$this->alias]['filename']);
		}
	print_r("b4 validate");
		return parent::beforeValidate($options);
	}
	



	/**
	 * Process the Upload
	 * @param array $check
	 * @return boolean
	 */
	public function processUpload($check=array()) {
		// deal with uploaded file
//		print_r("in process upload");
		if (!empty($check['filename']['tmp_name'])) {
			// check file is uploaded
			if (!is_uploaded_file($check['filename']['tmp_name'])) {
				return FALSE;
			}
			// build full filename
			$filename = WWW_ROOT . $this->uploadDir . DS . Inflector::slug(pathinfo($check['filename']['name'], PATHINFO_FILENAME)).'.'.pathinfo($check['filename']['name'], PATHINFO_EXTENSION);
			// @todo check for duplicate filename
			// try moving file
			if (!move_uploaded_file($check['filename']['tmp_name'], $filename)) {
				return FALSE;
			// file successfully uploaded
			} else {
				// save the file path relative from WWW_ROOT e.g. uploads/example_filename.jpg
				$this->data[$this->alias]['filepath'] = str_replace(DS, "/", str_replace(WWW_ROOT, "", $filename) );
			}
		}
		return TRUE;
	}





	/**
	 * Before Save Callback
	 * @param array $options
	 * @return boolean
	 */
	public function beforeSave($options = array()) {
		// a file has been uploaded so grab the filepath
		if (!empty($this->data[$this->alias]['filepath'])) {
			$this->data[$this->alias]['filename'] = $this->data[$this->alias]['filepath'];
		}
		print_r("b4save");
		return parent::beforeSave($options);
	}

	
	public function afterSave($created,$options = array()){
	 if (!$created) {
            $this->error = 'My error message';
     }
	else{
	print_r("after save....");
	$video_id=$this->data['Contact']['id'];
	print_r($video_id);

/*

	$QueuedTask = ClassRegistry::init('Queue.QueuedTask');
	$QueuedTask->createJob('Convert', array('video_id' => $video_id));



	$ffmpeg_path="/usr/bin/ffmpeg";
    $this->Contact->id = $id;
	$contact=$this->Contact->read();
	$in =WWW_ROOT.$contact['Contact']['filename'];
	$out=WWW_ROOT.'uploads/converted/'.explode( '/', $contact['Contact']['filename'] )[1];
//		$in=WWW_ROOT.'uploads/small.mp4';
//		$out=WWW_ROOT.'uploads/converted/small_converted.mp4';
//		$command = $ffmpeg_path. " -i $in -c:a copy -c:v copy -movflags faststart $out ";
	$command = $ffmpeg_path. " -i $in -c:a copy -c:v copy -movflags faststart $out 2>&1; echo $?";
	print_r($command);
	$action=shell_exec($command);
//		print_r(".. action:..");
//		print_r($action);
	$this->Contact->saveField('status', 'converted');
	$this->Contact->saveField('convertedpath', 'uploads/converted/'.explode( '/', $contact['Contact']['filename'] )[1]);


*/


	}
	}




}





