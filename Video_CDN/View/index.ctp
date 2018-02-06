<h2>Videos uploaded</h2>
<div>
<div class="row">
<button class="btn btn-info">
<?= $this->Html->link('Add Video', ['action' => 'add']) ?>
</button>
</div>


<?php $source_file="https://s3.amazonaws.com/convertedfilescdn/videos/video_cdn_1.mp4";
	echo $this->Html->media(
			array($source_file,
						array('src'=>$source_file,'type' => "video/mp4",'text' => 'Fallback text.. testing','width'=>"80px")),
			array('autoplay','controls')
			); ?>

<!--

<h4>Dummy videos</h4>
<?php foreach ($contacts as $contact): ?>

<div class="col-md-4">
	
	<p><?php $source_file=WWW_ROOT.$contact['Contact']['filename']; ?></p>  

<?php 
echo $this->Html->media(
			array($source_file,
						array('src'=>$source_file,'type' => "video/mp4",
					//	'pathPrefix'=>WWW_ROOT, 
	'fullBase'=>true,
					'text' => 'Fallback text.. testing','width'=>'240px')),
			array('autoplay','controls')
			); ?>
</div>


   <?php endforeach; ?>
->
</div>
