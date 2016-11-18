<?php

namespace nilsburg;


use Google\Cloud\Vision\VisionClient;
use GuzzleHttp\Client;

class PhpVision
{
    public $config = [
        'microsoft-vision' => [
            'api_key' => false,
            'url' => 'https://api.projectoxford.ai/vision/v1.0/analyze',
            'visualFeatures' => [
                'Categories',
                'Description',
                'Faces',
                'Tags',
                'Color',
                'Adult',
                'ImageType'
            ],
            'language' => 'en'
        ],
        'google-vision' => [
            'projectId' => false,
            'types' => [
                'FACE_DETECTION',
                'LOGO_DETECTION',
                'LANDMARK_DETECTION',
                'FACE_DETECTION',
                'TEXT_DETECTION',
                'LABEL_DETECTION',
                'IMAGE_PROPERTIES'
            ]
        ]
    ];

    public function __construct($config = [])
    {
        $this->config = array_replace_recursive($this->config, $config);
    }

    public function mVision($data = [])
    {
        $config = $this->config['microsoft-vision'];
        $visualFeatures = $config['visualFeatures'];
        $requestUrl = $config['url'] . "?" . "visualFeatures=" . implode(",",
                $visualFeatures) . "&language=" . $config['language'];
        if ($config['api_key']) {
            $client = new Client();
            $contentType = isset($data['url']) ? 'application/json' : 'application/octet-stream';
            $headers = [
                'Content-Type' => $contentType,
                'Ocp-Apim-Subscription-Key' => $config['api_key']
            ];
            $body = isset($data['url']) ? json_encode(['url' => $data['url']]) : (isset($data['body']) ? $data['body'] : false);
            if (!$body) {
                throw new \Exception("No input");
            }
            $response = $client->request('POST', $requestUrl, [
                'headers' => $headers,
                'body' => $body
            ]);
            $responseContent = $response->getBody()->getContents();
            if ($response->getHeader('Content-Type')[0] == 'application/json; charset=utf-8') {
                $responseContent = json_decode($responseContent, true);
            }
            return $responseContent;
        }
        return ['error' => true];
    }

    public function gVision($img)
    {
        $config = $this->config['google-vision'];
        if ($config['projectId']) {
            $vision = new VisionClient([
                'projectId' => $config['projectId']
            ]);
            $image = $vision->image($img, $config['types']);
            $res = $vision->annotate($image);
            return $res;
        }
        return ['error' => true];
    }
}