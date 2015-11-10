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

    public function indexAction($room_name)
    {
        return $this->twig->render('index.twig', array(
            'room_name' => $room_name
        ));
    }

    public function getRoomAction($room_name)
    {
        //print_r($room_name); die;

        $client = new Google_Client();
        $client->setApplicationName("Meeting_Room_Display");
        $client->setDeveloperKey($this->config['google']['api_key']);

        // Try to find the room
        $room_display_name = $this->config['rooms'][$room_name]['name'];
        $calendar_url = $this->config['rooms'][$room_name]['address'];

        //$calendar_url = 'shawmutdelivers.com_35373038323139323533@resource.calendar.google.com';

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

        function getMinutesUntilNextEvent($event)
        {
            $first_event_start = $event->getStart()->getDateTime();
            $first_event_start_datetime = new \DateTime($first_event_start);

            $interval = $first_event_start_datetime->diff(new \DateTime());
            //print_r($interval); die;

            $minutes_until_next_event =  $interval->format("%i");

            return $minutes_until_next_event;
        }
        function getTimeUntilNextEvent($event)
        {
            $first_event_start = $event->getStart()->getDateTime();
            $first_event_start_datetime = new \DateTime($first_event_start);

            $interval = $first_event_start_datetime->diff(new \DateTime());
            //print_r($interval); die;
            if($interval->h > 0){
                $time_until_next_event =  $interval->format("%h hours, %i minutes");
            } else {
                $time_until_next_event =  $interval->format("%i minutes");
            }

            return array(
                'first_event_start' => $first_event_start,
                'time_until_next_event' => $time_until_next_event
            );
        }

        function getTimeUntilCurrentEventComplete($event)
        {
            $current_event_end = $event->getEnd()->getDateTime();
            $current_event_end_datetime = new \DateTime($current_event_end);

            $interval = $current_event_end_datetime->diff(new \DateTime());

            if($interval->h > 0){
                $time_remaining =  $interval->format("%h hours, %i minutes");
            } else {
                $time_remaining =  $interval->format("%i minutes");
            }

            return $time_remaining;
        }

        $no_event = array(
            'first_event_start' => false,
            'time_until_next_event' => false
        );

        // Get first event, may be in progress
        if(isset($events[0])){
            $next_event = getTimeUntilNextEvent($events[0]);
        } else {
            $next_event = $no_event;
        }

        $time_remaining = null;

        // Determine if an event is in progress or not
        if($next_event['first_event_start'] < $now && $next_event['first_event_start'] != null){
            $event_in_progress = 'room-busy';
            // Next event is actually the further one
            if(isset($events[1])) {
                $next_event = getTimeUntilNextEvent($events[1]);
            } else {
                $next_event = $no_event;
            }
            // Find time remaining
            if(isset($events[0])){
                $time_remaining = getTimeUntilCurrentEventComplete($events[0]);

                // Determine if the busy room will be used again soon
                if(isset($events[1]) && getMinutesUntilNextEvent($events[1]) <= 10){
                    $event_in_progress = 'room-busy-soon';
                }
            }


        } else if(isset($events[0]) && ( getMinutesUntilNextEvent($events[0]) <= 10 ) ){
            $event_in_progress = 'room-soon';
        } else {
            $event_in_progress = 'room-free';
        }


        return $this->twig->render('room_display.twig', array(
            'events' => $events,
            'room_display_name' => $room_display_name,
            'time_until_next_event' => $next_event['time_until_next_event'],
            'event_in_progress' => $event_in_progress,
            'time_remaining' => $time_remaining
        ));
    }
}
