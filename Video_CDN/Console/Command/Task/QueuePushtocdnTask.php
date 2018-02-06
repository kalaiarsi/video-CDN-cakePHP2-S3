<?php
/**
 * @author MGriesbach@gmail.com
 * @license http://www.opensource.org/licenses/mit-license.php The MIT License
 * @link http://github.com/MSeven/cakephp_queue
 */
App::uses('QueueTask', 'Queue.Console/Command/Task');
require 'aws_s3_php/aws-autoloader.php';
/**
 * A Simple QueueTask example.
 *
 */
class QueuePushtocdnTask extends QueueTask {

/**
 * ZendStudio Codecomplete Hint
 *
 * @var QueuedTask
 */
	public $QueuedTask;

/**
 * Timeout for run, after which the Task is reassigned to a new worker.
 *
 * @var int
 */
	public $timeout = 10;

/**
 * Number of times a failed instance of this task should be restarted before giving up.
 *
 * @var int
 */
	public $retries = 1;

/**
 * Stores any failure messages triggered during run()
 *
 * @var string
 */
	public $failureMessage = '';

/**
 * Example add functionality.
 * Will create one example job in the queue, which later will be executed using run();
 *
 * @return void
 */
	public function add() {
		$this->out('Push-to-CDN task.');
		$this->hr();
		$this->out('You can find the sourcecode of this task in: ');
		$this->out(__FILE__);
		$this->out(' ');
		/*
		 * Adding a task of type 'example' with no additionally passed data
		 */
		if ($this->QueuedTask->createJob('Example', null)) {
			$this->out('OK, job created, now run the worker');
		} else {
			$this->err('Could not create Job');
		}
	}

/**
 * Example run function.
 * This function is executed, when a worker is executing a task.
 * The return parameter will determine, if the task will be marked completed, or be requeued.
 *
 * @param array $data The array passed to QueuedTask->createJob()
 * @param int $id The id of the QueuedTask
 * @return bool Success
 */
	public function run($data, $id = null) {
		$this->hr();
		$this->out('Pushing converted video file to CDN.');
		$this->hr();
		$this->out($data['outputfilename']);




		$AWS_ACCESS_KEY_ID="AxxxxA";  
		$AWS_SECRET_ACCESS_KEY="Cxxxxa";   // -------------------------------------------------------- push to staging/prod server cnfig files..
 		putenv("AWS_ACCESS_KEY_ID=$AWS_ACCESS_KEY_ID");
		putenv("AWS_SECRET_ACCESS_KEY=$AWS_SECRET_ACCESS_KEY");   	
		$sharedConfig = [
			'region'  => 'us-east-1',
			'version' => 'latest'
		];
		$sdk = new Aws\Sdk($sharedConfig);
		$client = $sdk->createS3();
		$bucket_name="convertedfilescdn";
		$source_file=WWW_ROOT.'uploads/converted/'.$data['outputfilename'];		
		$destination_path='videos/'.$data['outputfilename'];

		$s3Client = $sdk->createS3();
		try
		{
		$result = $s3Client->putObject([
			'Bucket' => $bucket_name,
			'Key'    => $destination_path, // Key-object pairs.. key : just the filename incl full path.
			'Body'   => file_get_contents($source_file)
		]);
		}
		catch (\Exception $e){
		//$this->out($e);
		$this->out('Error in Pushing video to CDN.');return False;}
			
		$this->out($result['ObjectURL']);

		$this->loadModel('Contact');
		$contact = $this->Contact->findById($data['contact']['Contact']['id']);
		$contact['Contact']['status'] = "pushedtoCDN";
		$contact['Contact']['cdnurl']=$result['ObjectURL'];
		$this->Contact->save($contact,$validate=false);

		$QueuedTask = ClassRegistry::init('Queue.QueuedTask');
		$QueuedTask->createJob('Deletepushedvideo', array('contact'=>$data['contact']));


/*
		$this->out('Video file pushed to CDN. Deleting video from Converted_videos folder');
		$command='rm '.$contact['Contact']['convertedpath'];
		$delete_action=shell_exec($command);
		$this->out($delete_action);
		if($delete_action==1){$this->out('Deleting video failed. Retry');return False;}
		$this->out('Completed deletion');
		$contact['Contact']['convertedpath'] = 'DeletedFile';
		$this->Contact->save($contact,$validate=false);
*/


/*		$this->out("  listing all videos under 'videos' folder.. ");

		$objects = $client->getIterator('ListObjects', array(
			"Bucket" => $bucket_name,
			"Prefix" => "videos/"
		)); 
			foreach ($objects as $object) { echo $object['Key'] . "<br>";	}
		$this->out($objects);

*/	

		return true;
	}
}
