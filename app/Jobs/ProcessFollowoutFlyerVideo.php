<?php

namespace App\Jobs;

use Str;
use App\Video;
use App\Notifications\VideoUploadFailed;
use Aws\MediaConvert\MediaConvertClient;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;

class ProcessFollowoutFlyerVideo implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     *
     * @var int
     */
    public $tries = 1;

    public $video;

    public $destination;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($videoId, $destination = 'videos')
    {
        $this->video = Video::with('uploader', 'file')->findOrFail($videoId);
        $this->destination = Str::start(Str::finish($destination, '/'), '/');
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        $client = new MediaConvertClient([
            'region' => env('AWS_DEFAULT_REGION'),
            'version' => '2017-08-29',
            'endpoint' => env('AWS_MEDIACONVERT_URL'),
        ]);

        $params = [
            'Role' => env('AWS_MEDIACONVERT_ROLE'),
            'Settings' => [
                'Inputs' => [
                    [
                        'FileInput' => 's3://' . env('AWS_BUCKET') . '/videos/raw/' . $this->video->filename,
                        'AudioSelectors' => [
                            'Audio Selector 1' => [
                                'Offset' => 0,
                                'DefaultSelection' => 'DEFAULT',
                                'ProgramSelection' => 1,
                            ],
                        ],
                        'VideoSelector' => [
                            'ColorSpace' => 'FOLLOW',
                            'Rotate' => 'AUTO',
                        ],
                        'TimecodeSource' => 'ZEROBASED',
                    ],
                ],
                'OutputGroups' => [
                    [
                        'Name' => 'MP4',
                        'OutputGroupSettings' => [
                            'Type' => 'FILE_GROUP_SETTINGS',
                            'FileGroupSettings' => [
                                'Destination' => 's3://' . env('AWS_BUCKET') . $this->destination,
                            ],
                        ],
                        'Outputs' => [
                            [
                                'ContainerSettings' => [
                                    'Container' => 'MP4',
                                ],
                                'AudioDescriptions' => [
                                    [
                                        'AudioTypeControl' => 'FOLLOW_INPUT',
                                        'CodecSettings' => [
                                            'Codec' => 'AAC',
                                            'AacSettings' => [
                                                'AudioDescriptionBroadcasterMix' => 'NORMAL',
                                                'Bitrate' => 96000,
                                                'CodecProfile' => 'LC',
                                                'CodingMode' => 'CODING_MODE_2_0',
                                                'RateControlMode' => 'CBR',
                                                'RawFormat' => 'NONE',
                                                'SampleRate' => 48000,
                                                'Specification' => 'MPEG4',
                                            ],
                                        ],
                                    ],
                                ],
                                'VideoDescription' => [
                                    'AntiAlias' => 'ENABLED',
                                    'CodecSettings' => [
                                        'Codec' => 'H_264',
                                        'H264Settings' => [
                                            'InterlaceMode' => 'PROGRESSIVE',
                                            'Bitrate' => 5000000,
                                        ],
                                    ],
                                    'Width' => 1080,
                                    'Height' => 1920,
                                ],
                            ],
                        ],
                    ],
                    [
                        'Name' => 'Apple HLS',
                        'OutputGroupSettings' => [
                            'Type' => 'HLS_GROUP_SETTINGS',
                            'HlsGroupSettings' => [
                                'Destination' => 's3://' . env('AWS_BUCKET') . $this->destination,
                                'SegmentLength' => 100000,
                                'MinSegmentLength' => 0,
                                'SegmentControl' => 'SINGLE_FILE'
                            ],
                        ],
                        'Outputs' => [
                            [
                                'ContainerSettings' => [
                                    'Container' => 'M3U8',
                                ],
                                'AudioDescriptions' => [
                                    [
                                        'AudioTypeControl' => 'FOLLOW_INPUT',
                                        'CodecSettings' => [
                                            'Codec' => 'AAC',
                                            'AacSettings' => [
                                                'AudioDescriptionBroadcasterMix' => 'NORMAL',
                                                'Bitrate' => 96000,
                                                'CodecProfile' => 'LC',
                                                'CodingMode' => 'CODING_MODE_2_0',
                                                'RateControlMode' => 'CBR',
                                                'RawFormat' => 'NONE',
                                                'SampleRate' => 48000,
                                                'Specification' => 'MPEG4',
                                            ],
                                        ],
                                    ],
                                ],
                                'VideoDescription' => [
                                    'AntiAlias' => 'ENABLED',
                                    'CodecSettings' => [
                                        'Codec' => 'H_264',
                                        'H264Settings' => [
                                            'InterlaceMode' => 'PROGRESSIVE',
                                            'Bitrate' => 5000000,
                                        ],
                                    ],
                                    'Width' => 1080,
                                    'Height' => 1920,
                                ],
                                'NameModifier' => '-hls',
                            ],
                        ],
                    ],
                    [
                        'Name' => 'Thumbnail',
                        'OutputGroupSettings' => [
                            'Type' => 'FILE_GROUP_SETTINGS',
                            'FileGroupSettings' => [
                                'Destination' => 's3://' . env('AWS_BUCKET') . $this->destination,
                            ],
                        ],
                        'Outputs' => [
                            [
                                'ContainerSettings' => [
                                    'Container' => 'RAW',
                                ],
                                'VideoDescription' => [
                                    'AntiAlias' => 'ENABLED',
                                    'CodecSettings' => [
                                        'Codec' => 'FRAME_CAPTURE',
                                        'FrameCaptureSettings' => [
                                            'FramerateNumerator' => 1,
                                            'FramerateDenominator' => 1,
                                            'MaxCaptures' => 1,
                                            'Quality' => 100
                                        ],
                                    ],
                                    'Width' => 1080,
                                    'Height' => 1920,
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        if (!$this->video->uploader->isAdmin()) {
            $params['Settings']['Inputs'][0]['InputClippings'][0]['EndTimecode'] = '00:00:05:00';
        }

        $result = $client->createJob($params);

        $job = $result->search('Job');
        $jobId = $job['Id'] ?? null;

        if (is_null($jobId)) {
            throw new \Exception('Error processing video upload', 500);
        }

        $this->video->aws_job_id = $jobId;
        $this->video->save();
    }

    /**
     * The job failed to process.
     *
     * @param  Exception  $exception
     * @return void
     */
    public function failed(\Exception $exception)
    {
        $this->video->uploader->notify(new VideoUploadFailed($this->video));

        $this->video->deleteVideo(true);
    }
}
