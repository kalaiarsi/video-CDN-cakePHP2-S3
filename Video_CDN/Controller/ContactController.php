<?php
App::uses('S3','Lib');
require 'aws_s3_php/aws-autoloader.php';

class ContactController extends AppController {
	public function initialize(){
        parent::initialize();
        $this->loadComponent('Flash');
        $this->loadModel('Files');
    }

	public function index(){
//				print_r("index");
		$this->set('contacts', $this->Contact->find('all'));


		$AWS_ACCESS_KEY_ID="AxxxxxA";  
		$AWS_SECRET_ACCESS_KEY="Cxxxxxa";   // -------------------------------------------------------- push to staging/prod server cnfig files..

//		getenv('DB_USER')
	//	putenv("UNIQID=$uniqid");
/*		$s3 = new Aws\S3\S3Client([
			'version' => 'latest',
			'region'  => 'us-east-1'
		]);
*/
 		putenv("AWS_ACCESS_KEY_ID=$AWS_ACCESS_KEY_ID");
		putenv("AWS_SECRET_ACCESS_KEY=$AWS_SECRET_ACCESS_KEY");   
		
		
		$sharedConfig = [
			'region'  => 'us-east-1',
			'version' => 'latest'
		];

		// Create an SDK class used to share configuration across clients.
		$sdk = new Aws\Sdk($sharedConfig);

		// Create an Amazon S3 client using the shared configuration data.
		$client = $sdk->createS3();
//		print_r($client);

		$bucket_name="convertedfilescdn";
		$destination_path="videos/video_cdn_8.mp4";
		$source_file=WWW_ROOT.'uploads/converted/small_mp4.mp4';

		$s3Client = $sdk->createS3();
		$result = $s3Client->putObject([
			'Bucket' => $bucket_name,
			'Key'    => $destination_path, // Key-object pairs.. key : just the filename incl full path.
			'Body'   => file_get_contents($source_file)
		]);
//		print_r("result of putobject");
//		print_r($result);

		print_r("  listing all videos under 'videos' folder.. ");
		$objects = $client->getIterator('ListObjects', array(
			"Bucket" => $bucket_name,
			"Prefix" => "videos/"
		)); 
			foreach ($objects as $object) {
			echo $object['Key'] . "<br>";
				}
		print_r($objects);

		
		}

	public function listofvideos(){
//				print_r("index");



		$AWS_ACCESS_KEY_ID="AxxxxA";  
		$AWS_SECRET_ACCESS_KEY="Cxxxxa";   // -------------------------------------------------------- push to staging/prod server cnfig files..

//		getenv('DB_USER')
	//	putenv("UNIQID=$uniqid");
/*		$s3 = new Aws\S3\S3Client([
			'version' => 'latest',
			'region'  => 'us-east-1'
		]);
*/
 		putenv("AWS_ACCESS_KEY_ID=$AWS_ACCESS_KEY_ID");
		putenv("AWS_SECRET_ACCESS_KEY=$AWS_SECRET_ACCESS_KEY");   
		
		
		$sharedConfig = [
			'region'  => 'us-east-1',
			'version' => 'latest'
		];

		// Create an SDK class used to share configuration across clients.
		$sdk = new Aws\Sdk($sharedConfig);

		// Create an Amazon S3 client using the shared configuration data.
		$client = $sdk->createS3();
//		print_r($client);

		$bucket_name="convertedfilescdn";
		$destination_path="videos/video_cdn_8.mp4";
		$source_file=WWW_ROOT.'uploads/converted/small_mp4.mp4';

		$s3Client = $sdk->createS3();
/*		$result = $s3Client->putObject([
			'Bucket' => $bucket_name,
			'Key'    => $destination_path, // Key-object pairs.. key : just the filename incl full path.
			'Body'   => file_get_contents($source_file)
		]);
		print_r("result of putobject");
		print_r($result);
*/
		print_r("  listing all videos under 'videos' folder.. ");
		$objects = $client->getIterator('ListObjects', array(
			"Bucket" => $bucket_name,
			"Prefix" => "videos/"
		)); 
//			foreach ($objects as $object) {
//			echo $object['Key'] . "<br>";
//				}
		print_r($objects);
		$this->set('videofiles', $objects);
		
		}

	public function add() {
		// form posted
		if ($this->request->is('post')) {
			// create
			$this->Contact->create();
			// attempt to save

			if ($this->Contact->save($this->request->data)) {
				$this->Session->setFlash('Your message has been submitted');


				$contact = $this->Contact->findById($this->Contact->id);

//				print_r("contact:");
				print_r($contact['Contact']['filename']);
				$ffmpeg_path='/usr/bin/ffmpeg';			//------------------------------------------- set manually	
				$QueuedTask = ClassRegistry::init('Queue.QueuedTask');
				$QueuedTask->createJob('Convert', array('contact' => $contact,'ffmpeg_path'=>$ffmpeg_path));
				$this->redirect(array('action' => 'listofvideos'));
			// form validation failed
			} else {
				// check if file has been uploaded, if so get the file path
				if (!empty($this->Contact->data['Contact']['filepath']) && is_string($this->Contact->data['Contact']['filepath'])) {
					$this->request->data['Contact']['filepath'] = $this->Contact->data['Contact']['filepath'];
				}
			}
		}
	}


	public function startconversion($id=6){
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


	}

}





/*
	 public function upload(){
        $uploadData = '';
        if ($this->request->is('post')) {
			print_r($this->request->data);
            if(!empty($this->request->data['Contact']['file']['name'])){
                $fileName = $this->request->data['Contact']['file']['name'];
                $uploadPath = 'uploads/files/';
                $uploadFile = $uploadPath.$fileName;
					print_r('c-f-tn');
					print_r($this->request->data['Contact']['file']['tmp_name']);
					print_r($uploadFile);
				$move_operation=move_uploaded_file($this->request->data['Contact']['file']['tmp_name'],$uploadFile);
				print_r('move');
				print_r($move_operation);
                if($move_operation){

                    $uploadData = $this->File->create(); //newEntity();
                    $uploadData->name = $fileName;
                    $uploadData->path = $uploadPath;
                    $uploadData->created = date("Y-m-d H:i:s");
                    $uploadData->modified = date("Y-m-d H:i:s");
                    if ($this->Files->save($uploadData)) {
                        $this->Flash->success(__('File has been uploaded and inserted successfully.'));
                    }else{
                        $this->Flash->error(__('Unable to upload file, please try again.'));
                    }
               }else{
                    $this->Flash->error(__('Unable to upload file, please try again.'));
                }
            }else{
				print_r($this->request->data['Contact']['file']['name']);
                $this->Flash->error(__('Please choose a file to upload.'));
            }
            
        }
        $this->set('uploadData', $uploadData);
        
        $files = $this->Files->find('all', ['order' => ['Files.created' => 'DESC']]);
        $filesRowNum = $files->count();
        $this->set('files',$files);
        $this->set('filesRowNum',$filesRowNum);

    }
   */ 









/*

		print_r("S3 started.. following is client:..  ");
		$access_key="AxxxxA";
		$secret_key="Cxxxxa";
		$client = new S3($access_key, $secret_key);
//		print_r($client);
		print_r("   ... client done ... PUT object");

		$bucket_name="convertedfilescdn";
		$source_file=WWW_ROOT.'uploads/converted/small_mp4.mp4';
		$destination_path="videos/video_cdn_4.mp4";
//		$source_file=APP."Controller/UsersController.php";
//		$destination_path="videos/controller.php";
		$response=$client->putObject($bucket_name,$destination_path,file_get_contents($source_file),
						array('Content-Type' => 'text/plain')); 

//		print_r($response);



		print_r("getting bucket info");
		$response=$client->getBucket($bucket_name);
		print_r($response);

		print_r("getting object info");
		$response=$client->getObjectInfo($bucket_name,'videos');
		print_r($response);

		$objects = $client->getIterator('ListObjects', array(
		"Bucket" => $bucket_name,
		"Prefix" => "videos/"
			)); 
		print_r("objects");


*/






/*
	public function convert(){
	// You probably will upload the video from a form 
     
    // Let’s say all of that is said and done and you have your video  
    // uploaded and have it’s path stored in the variable $path 
     
    // Let’s also say that you have a variable set with the output path 
    // that the converted video will be stored named $out_path 
     
    // The first this we need to do is convert the video 
	$path="uploads/lionsample.mp4";
	$out_path="uploads/converted/lionsampleconverted.mp4";
    $this->VideoEncoder->convert_video($path, $out_path, 480, 360); 
     
    // Then we need to set the buffer on the converted video 
    $this->VideoEncoder->set_buffering($out_path); 
     
    // We can now get some information back about the converted video that 
    // can be stored in a database for further use 
    $duration = $this->VideoEncoder->get_duration($out_path); 
    $filesize = $this->VideoEncoder->get_filesize($out_path); 
     
    // We can also grab a screenshot from the video as a jpeg 
    // and store it for future use. 
	$path_to_save_image="uploads/converted/lionsamplevideo_screenshot";
    $this->VideoEncoder->grab_image($out_path, $path_to_save_image); 

	}

*/







