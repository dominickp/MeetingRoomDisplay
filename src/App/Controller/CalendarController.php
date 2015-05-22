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

        $midnight_datetime = new \DateTime('tomorrow midnight');
        $midnight =  $midnight_datetime->format(\DateTime::ATOM);

        $now = date(\DateTime::ATOM);

        // Get events
        $service = new Google_Service_Calendar($client);
        $optParams = array(
            'timeMax' => $midnight,
            'timeMin' => $now,
            "singleEvents"=>true,
            'orderBy' => 'startTime'
        );

        $events = $service->events->listEvents($calendar_url, $optParams);

//print_r($events[1]); die;


        // Find the time start of an event

        function getTimeUntilNextEvent($event)
        {
            $first_event_start = $event->getStart()->getDateTime();
            $first_event_start_datetime = new \DateTime($first_event_start);

            $interval = $first_event_start_datetime->diff(new \DateTime());
            $time_until_next_event =  $interval->format("%h hours, %i minutes");

            return array(
                'first_event_start' => $first_event_start,
                'time_until_next_event' => $time_until_next_event
            );
        }

        $no_event = array(
            'first_event_start' => false,
            'time_until_next_event' => false
        );

        // Get first event, may be in progress
        if($events[0]){
            $next_event = getTimeUntilNextEvent($events[0]);
        } else {
            $next_event = $no_event;
        }




        // Determine if an event is in progress or not
        if($next_event['first_event_start'] < $now){
            $event_in_progress = 'yes';
            // Next event is actually the further one
            if($events[1]) {
                $next_event = getTimeUntilNextEvent($events[1]);
            } else {
                $next_event = $no_event;
            }
        } else {
            $event_in_progress = 'no';
        }



        return $this->twig->render('index.twig', array(
            'events' => $events,
            'time_until_next_event' => $next_event['time_until_next_event'],
            'event_in_progress' => $event_in_progress
        ));
    }
}
