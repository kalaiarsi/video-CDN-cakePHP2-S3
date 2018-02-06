<?php
/**
 * @author MGriesbach@gmail.com
 * @license http://www.opensource.org/licenses/mit-license.php The MIT License
 * @link http://github.com/MSeven/cakephp_queue
 */
App::uses('QueueTask', 'Queue.Console/Command/Task');

/**
 * A Simple QueueTask example.
 *
 */
class QueueConvertTask extends QueueTask {

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
		$this->out('Convert-Video-to-MP4 task.');
		$this->hr();
			$this->out(__FILE__);
		$this->out(' ');
		/*
		 * Adding a task of type 'example' with no additionally passed data
		 */
		if ($this->QueuedTask->createJob('Convert', null)) {
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
//		$this->out('CakePHP Queue Example task.');
		$this->hr();
//		$this->out(' BRAINGROOM.....contact:  ...............');
		$this->out($data['contact']['Contact']['filename']);
		
		$this->loadModel('Contact');
		$ffmpeg_path=$data['ffmpeg_path'];    
		$inputfile=WWW_ROOT.$data['contact']['Contact']['filename'];
		$newfile=explode( '/', $data['contact']['Contact']['filename'])[1];
		$newfile=explode( '.', $newfile)[0];
		$newfile=preg_replace("/[^A-Za-z0-9]/", '', $newfile );
		$random_suffix=substr(str_shuffle(str_repeat($x='0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ',
				ceil(10/strlen($x)) )),1,35);
		$outputfilename=substr($newfile.$random_suffix,0,35).'.mp4';
		$converted_path='uploads/converted/'.$outputfilename;
		$outputfilepath=WWW_ROOT.$converted_path;
		

//		$command = $ffmpeg_path." -i $inputfile -c:a copy -c:v copy -movflags faststart $outputfile 2>&1; echo $?";
		$command = $ffmpeg_path." -i $inputfile -c:a copy -c:v libx264 -movflags faststart $outputfilepath 2>&1; echo $?";  		// works
//$command="MP4Box -add $inputfile -inter 500 $outputfilepath";
//ffmpeg -i ~/Videos/moto.mp4 -c:a copy -c:v libx264 -movflags faststart ~/Videos/moto_copy_1.mp4
//		$command = $ffmpeg_path." -i $inputfile -c:a copy -c:v copy -movflags faststart $outputfilepath -hide_banner";
		$this->out('video conversion started');
		$this->out($command);
		$convert_action=shell_exec($command);
//		$this->out("shell exec : action result..");
//		$this->out($convert_action);
		if($convert_action==1){$this->out('video conversion failed');return False;}
		else{$this->out('video conversion done successfully');}
		$contact=$data['contact'];
		$this->out($contact['Contact']['filename']);

		
		$contact = $this->Contact->findById($data['contact']['Contact']['id']);
		$contact['Contact']['status'] = "converted";
		$contact['Contact']['convertedpath']=$outputfilepath;
		$this->Contact->save($contact,$validate=false);
		$this->out("Updated \"status\" field on DB");


		$QueuedTask = ClassRegistry::init('Queue.QueuedTask');
		$QueuedTask->createJob('Pushtocdn', array('contact'=>$data['contact'],'outputfilename' => $outputfilename));


		return true;
	}
	



/*
		public function run($data, $id = null) {
		$this->hr();
		$this->out('CakePHP Queue Example task.');
		$this->hr();
		$this->out(' BRAINGROOM.....contact:  ...............');
		$this->out($data['contact']['Contact']['filename']);
		
		$this->loadModel('Contact');
		$ffmpeg_path=$data['ffmpeg_path'];    
		$inputfile=WWW_ROOT.$data['contact']['Contact']['filename'];
		$newfile=explode( '/', $data['contact']['Contact']['filename']);
		$outputfile=WWW_ROOT.'uploads/converted/'.$newfile[1];
//		$command = $ffmpeg_path." -i $inputfile -c:a copy -c:v copy -movflags faststart $outputfile 2>&1; echo $?";
		$command = $ffmpeg_path." -i $inputfile -c:a copy -c:v copy -movflags faststart $outputfile";
		$this->out($command);
		$action=shell_exec($command);
		$contact=$data['contact'];
		$this->out($contact['Contact']['filename']);
		$this->out("saving field on sql");
		
		$contact = $this->Contact->findById($data['contact']['Contact']['id']);
		print_r("before saving");
		print_r($contact['Contact']['status']);
		$contact['Contact']['status'] = "converted";

		$this->Contact->save($contact,$validate=false);
		
		print_r("after saving");
		print_r($contact['Contact']['status']);

		$this->out(' ->Success, the Example Job was run.<-');
		$this->out(' ');
		$this->out(' ');
		return true;
	}
*/

}
