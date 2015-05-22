<?php

namespace App\Controller;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Google_Client;
use Google_Service_Calendar;


class CalendarController
{

    /**
     * @var \Twig_Environment
     */
    private $twig;

    /**
     * @var LoggerInterface
     */
    private $logger;

    private $config;

    public function __construct(\Twig_Environment $twig, LoggerInterface $logger, $config)
    {
        $this->twig = $twig;
        $this->logger = $logger;
        $this->config = $config;
    }

    public function indexAction()
    {

        $client = new Google_Client();
        $client->setApplicationName("Meeting_Room_Display");
        $client->setDeveloperKey($this->config['google']['api_key']);

        $calendar_url = 'shawmutdelivers.com_35373038323139323533@resource.calendar.google.com';

        $real = new \DateTime('tomorrow midnight');
        $midnight =  $real->format(\DateTime::ATOM);

        $now = date(\DateTime::ATOM);

        //print_r($now.$midnight);die;

        $service = new Google_Service_Calendar($client);
        $optParams = array(
            'timeMax' => $midnight,
            'timeMin' => $now,
            "singleEvents"=>true,
        );

        $events = $service->events->listEvents($calendar_url, $optParams);
//
//        $captured_events = array();
//
//        while(true) {
//            foreach ($events->getItems() as $event) {
//                //echo $event->getSummary();
//                //print_r(json_encode($event));
//                $captured_events[] = $event;
//            }
//            $pageToken = $events->getNextPageToken();
//            if ($pageToken) {
//                $optParams = array('pageToken' => $pageToken);
//                $events = $service->calendarList->listCalendarList($optParams);
//            } else {
//                break;
//            }
//        }



        //$results = $service->calendars->get($calendar_url);
        //$results = $service->volumes->listVolumes('Henry David Thoreau', $optParams);

        //print_r(json_encode($results)); die;

        $test = new \Google_Service_Calendar_Event();
        $start = $test->getStart();
            //$start->


        return $this->twig->render('index.twig', array(
            'events' => $events
        ));
    }
}
