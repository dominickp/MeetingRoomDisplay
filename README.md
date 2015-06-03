Meeting Room Display
===============
This project displays event details for a conference room using Google Calendar. It's built in PHP with Silex and uses Google's Service API. Every minute, the web page makes an AJAX request to update its content. Using a config file, you can easily set up multiple meeting rooms which have their own HTTP routes. This project was heavily inspired by [google-calendar-display](https://github.com/course-hero/google-calendar-display).

## Examples


![Free Room](https://i.imgur.com/DwF3MS7.png "Free Room")
![Soon Room](https://i.imgur.com/OHb41bf.png "Soon Room")
![Busy Room](https://i.imgur.com/E8dhIC0.png "Busy Room")
![Installed](https://i.imgur.com/CEbTMzZ.jpg "Installed")

## Installation\Usage
Clone the repo, download Composer to the project directory and run:

  ```php composer.phar install```

Then, create the file *app/config/parameters.yml* which should have the following structure:

```
google:
    api_key: [YOUR_GOOGLE_API_KEY]
rooms:
    green-room:
        name: Green Room
        address: [YOUR_CALENDAR_ADDRESS]
    client-room:
        name: Client Room
        address: [etc...]
```

Since this does not use OAuth, you'll need to make sure your resource calendars using this are publically available. You should then be able to point a browser to http://your-host/web/{room_key}. So using the example above, to see the Green Room calendar, we would point to *http://your-host/web/green-room*.
