<?php

namespace App\Console\Commands;

use Storage;
use App\Video;
use App\Notifications\VideoUploadFailed;
use Aws\MediaConvert\MediaConvertClient;
use Illuminate\Console\Command;

class UpdateProcessedVideos extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'videos:update';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update Video models that were processed by AWS MediaConvert';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $videos = Video::unprocessed()->whereNotNull('aws_job_id')->get();

        $client = new MediaConvertClient([
            'region' => env('AWS_DEFAULT_REGION'),
            'version' => '2017-08-29',
            'endpoint' => env('AWS_MEDIACONVERT_URL'),
        ]);

        foreach ($videos as $video) {
            $result = $client->getJob(['Id' => $video->aws_job_id]);

            $job = $result->search('Job');

            if ($job['Status'] === 'COMPLETE') {
                $video->markAsProcessed();

                $this->line('Video #' . $video->id . ' has been successfully processed.');
            } elseif ($job['Status'] === 'ERROR') {
                $video->uploader->notify(new VideoUploadFailed($video));

                $video->deleteVideo(true);

                $this->error('Video #' . $video->id . ' failed to process and has been deleted.');
            }
        }
    }
}
