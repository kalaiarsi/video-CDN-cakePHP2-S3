<h2>Videos uploaded</h2>

<div>

	<div class="row">
	<button class="btn btn-info">
	<?= $this->Html->link('Add Video', ['action' => 'add']) ?>
	</button>
	</div>




	<h4>CDN videos</h4>
	
	<?php foreach ($videofiles as $videofile): ?>



		<div class="col-md-4">
<?php if (substr($videofile['Key'], -4) === '.mp4') { ?>


			<div class="row">
			<div style="padding:10px 10px; margin: 10px 10px;">

			<p style="word-break: break-all;"><?php $source_file="https://s3.amazonaws.com/convertedfilescdn/".$videofile['Key']; 
						echo $source_file; ?></p>  


			<?php echo $this->Html->media($source_file, array(
			//				'src'=>$source_file.'.ogg', 	'type' => "video/ogg; codecs='theora, vorbis'",
									 'type'=>'video/mp4',
									'width'=>'180','height'=>'120','text' => 'Video missing','controls'=>true,'autoplay'=>true,
			)); ?>


<?php } ?>
			</div>
			</div>
		</div>


   <?php endforeach; ?>

</div>
