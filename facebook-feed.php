<?php
namespace Grav\Plugin;

use \Grav\Common\Plugin;
use Grav\Common\Data\Data;
use Grav\Common\Page\Page;
use Grav\Common\GPM\Response;
use Grav\Common\Theme;
use \DateTime;

class FacebookFeedPlugin extends Plugin {

    // Set Twig Template
    private $template_post_html = 'partials/facebook-feed.html.twig';

     // Initialize configuration.
    public function onPluginsInitialized() {
        $this->enable(['onTwigTemplatePaths' => ['onTwigTemplatePaths', 0],
            'onTwigInitialized' => ['onTwigInitialized', 0]]);
    }

     // Add Twig Extensions.
    public function onTwigInitialized() {
        $this->grav['twig']->twig->addFunction(new \Twig_SimpleFunction('facebook_feed',
            [$this, 'getFacebookFeed']));
    }

    //Add current directory to twig lookup paths.
    public function onTwigTemplatePaths() {
        $this->grav['twig']->twig_paths[] = __DIR__ . '/templates';
    }


    public function getFacebookFeed() {
        // Get Configs from blueprint
        $pageId = $this->config->get('plugins.facebook-feed.fb_settings.page_id');
        $accessToken = $this->config->get('plugins.facebook-feed.fb_settings.token');
        $limit = $this->config->get('plugins.facebook-feed.fb_settings.limit');

        // Generate API with Page Id and Token
        $url = 'https://graph.facebook.com/v4.0/' . $pageId . '/posts?fields=attachments,created_time,message&access_token=' . $accessToken . '&limit=' . $limit;

        // Check 200 Ok
        function get_http_response_code($url) {
            $headers = get_headers($url);
            return substr($headers[0], 9, 3);
        }
        $get_http_response_code = get_http_response_code($url);

        if ( $get_http_response_code == 200 ) {
            $response = Response::get($url);
            $result = json_decode($response);

        // Get Data from Facebook Feed
        foreach ($result->data as $val) {

            //Get Post Date
            $postDate = date("d.m.Y", strtotime($val->created_time));

            //Get Messages
            if(property_exists($val, 'message')) {
                $message = $val->message;
            }

            // Get Album Images
            $album = [];
            if(property_exists($val, 'attachments')) {
                if(property_exists($val->attachments->data[0], 'subattachments')) {
                    $subData = $val->attachments->data[0]->subattachments->data;
                    foreach ($subData as $key) {
                        $albumImages = $key->media->image->src;
                        array_push($album, $albumImages);
                    }
                }
            }

            // Get post Image
            $image = false;
            if(property_exists($val, 'attachments') && property_exists($val->attachments->data[0], 'media')) {
                $image = $val->attachments->data[0]->media->image->src;
            }

            //Get post Url
            if(property_exists($val, 'attachments') && property_exists($val->attachments->data[0], 'url')) {
                $url = $val->attachments->data[0]->target->url;
            }


            // Store feed Data inside Arrays
            $values['values'][] = [
                'postDate' => $postDate,
                'message' => $message,
                'image' => $image,
                'album' => $album,
                'url' => $url,
            ];
        }

        // Get Plugin Config
        $pluginConfig = [
            'headline' => $this->config->get('plugins.facebook-feed.fb.headline'),
            'pageId' => $this->config->get('plugins.facebook-feed.fb_settings.page_id'),
            'postLinkTitle' => $this->config->get('plugins.facebook-feed.fb.postLinkTitle'),
            'postLinkAlt' => $this->config->get('plugins.facebook-feed.fb.postLinkAlt'),
            'mainLinkTitle' => $this->config->get('plugins.facebook-feed.fb.mainLinkTitle'),
            'mainLinkAlt' => $this->config->get('plugins.facebook-feed.fb.mainLinkAlt'),
            'imgFallback' => $this->config->get('plugins.facebook-feed.fb.imgFallbackSelect'),
            'imgFallbackAlt' => $this->config->get('plugins.facebook-feed.fb.imgFallbackAlt'),
            'imgFallbackTitle' => $this->config->get('plugins.facebook-feed.fb.imgFallbackTitle'),
            'theme' => $this->config->get('system.pages.theme')
        ];

            $output = $this->grav['twig']->twig()->render($this->template_post_html, array(
            'data' => $values,
            'pluginConfig' => $pluginConfig
        ));
        return $output;

        } else {
            echo '
                <p style="padding:20px;background:#e2001a;color:#fff;text-align:center;font-size:1.5em">
                    Ooops, Looks like your Page Id and/or Access Token are missing or not correct
                </p>
            ';
        }
    }

}
