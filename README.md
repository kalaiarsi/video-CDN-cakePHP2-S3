CakePHP2 project for video CDN(S3)

Video CDN: Video uploaded by user, converted to mp4 format, pushed to S3 bucket(CDN), local files deleted. Three Queues(1: converting to mp4, 2: pushing to S3, 3: deleting local file) setup and tasks done asynchronously.
Cakephp plugin used. Amazon s3 sdk for php: http://docs.aws.amazon.com/aws-sdk-php/v3/guide/getting-started/installation.html
