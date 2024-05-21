<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class FeedController extends AbstractController
{
    public function getRssFeedAction(): Response
    {
        $url = 'https://declablog.webmarketing-vaniseo.com/feed/';

        libxml_use_internal_errors(true);
        $content = file_get_contents($url);
        $xml = simplexml_load_string($content);
        libxml_clear_errors();

        if ($xml === false) {
            return new JsonResponse(['error' => 'Failed to parse RSS feed'], Response::HTTP_BAD_REQUEST);
        }

        $items = [];
        for ($i = 0; $i < count($xml->channel->item); $i++) {
            $item = $xml->channel->item[$i];
            $description = (string) $item->description;
            $pattern = '/<img[^>]+src="([^"]+)"/';
            preg_match($pattern, $description, $matches);
            $image = $matches[1] ?? '';
            $items[] = [
                'title' => (string) $item->title,
                'link' => (string) $item->link,
                'description' => $description,
                'pubDate' => (string) $item->pubDate,
                'image' => $image,
            ];
        }

        return new JsonResponse($items);
    }
}
