<?php

// Function to update AP style
function update_ap_style($get_ap) {
  if($get_ap == 'am'){
    return ' a.m.';
  }else if($get_ap == 'pm'){
    return ' p.m.';
  }
}

function render_event_feed($attributes) {
  $val = '';
  if (function_exists('curl_version')) :
    $className = '';
    if (array_key_exists('className', $attributes)) {
      $className = ' ' . $attributes['className'];
    }

    $textAlignmentValue = '';
    if (array_key_exists('textAlignment', $attributes) && $attributes['textAlignment'] !== '') {
      $textAlignmentValue = 'ua_align--' . $attributes['textAlignment'];
    }

    $alignClass = '';
    if (array_key_exists('align', $attributes)) {
      if ($attributes['align'] === 'wide'){
        $alignClass = ' alignwide';
      } else if($attributes['align'] === 'full'){
        $alignClass = ' alignfull';
      }
    }
    $columnStyles = '';
    if($attributes['maxColumns'] > 0){
      if( $columnStyles == ''){
        $columnStyles = 'style=';
      }
      $columnStyles .='--grid-column-count:'.$attributes['maxColumns'];
    }

    $count = 1;
    $limiter = 4;
    if (array_key_exists('eventNum', $attributes)) {
      $limiter = $attributes['eventNum'];
    }

    if ($limiter > 50) {
      $limiter = 50;
    }


    $calendarURL = 'https://calendar.ua.edu';
    if (array_key_exists('calendarURL', $attributes) && ( $attributes['calendarURL'] != '' ) ) {
      $calendarURL = $attributes['calendarURL'];
    }

    date_default_timezone_set('America/Chicago');
    $dateRange = '1';

    if (array_key_exists('dateRange', $attributes)) {
      $dateRange = $attributes['dateRange'];
    }

    $today = new DateTime();
    $future = new DateTime();
    $future->add(new DateInterval('P' . $dateRange . 'M'));
    $todayStamp = $today->getTimestamp();
    $futureStamp = $future->getTimestamp();
    $todayDate = date('Y-m-d', $todayStamp);
    $futureDate = date('Y-m-d', $futureStamp);

    $matchAll = true;
    if (array_key_exists('matchAll', $attributes)) {
      $matchAll = $attributes['matchAll'];
    }

    $groupQuery = '';
    if ($matchAll) {
      $departmentOrGroup = array_key_exists('departmentOrGroup', $attributes) ? $attributes['departmentOrGroup'] : 'department';
      if ($departmentOrGroup === 'group') {
        if (array_key_exists('singleGroup', $attributes) && $attributes['singleGroup'] !== '') {
          $groupQuery = '&group_id[]=' . $attributes['singleGroup'];
        }
      } else {
        if (array_key_exists('singleDepartment', $attributes) && $attributes['singleDepartment'] !== '') {
          $groupQuery = '&group_id[]=' . $attributes['singleDepartment'];
        }
      }
    } else {
      if (array_key_exists('multipleDepartments', $attributes) && is_array($attributes['multipleDepartments'])) {
        foreach ($attributes['multipleDepartments'] as $id) {
          $groupQuery .= '&group_id[]=' . $id;
        }
      }
      if (array_key_exists('multipleStudentGroups', $attributes) && is_array($attributes['multipleStudentGroups'])) {
        foreach ($attributes['multipleStudentGroups'] as $id) {
          $groupQuery .= '&group_id[]=' . $id;
        }
      }
    }

    $audienceQuery = '';
    if (array_key_exists('audience', $attributes) && $attributes['audience'] !== '') {
      $audienceQuery = '&type[]=' . $attributes['audience'];
    }

    $typeQuery = '';
    if (array_key_exists('eventType', $attributes) && $attributes['eventType'] !== '') {
      $typeQuery = '&type[]=' . $attributes['eventType'];
    }

    $topicQuery = '';
    if (array_key_exists('topic', $attributes) && $attributes['topic'] !== '') {
      $topicQuery = '&type[]=' . $attributes['topic'];
    }

    $match = '&require_all=true';
    if (!$matchAll) {
      $match = '&match=any';
    }

    $query = $typeQuery . $audienceQuery . $topicQuery;
    $feedURL = 'https://calendar.ua.edu/api/2/events?start=' . $todayDate . '&end=' . $futureDate . $groupQuery . $query . '&pp=50' . $match;

    $curl = curl_init();
    curl_setopt_array($curl, [
      CURLOPT_RETURNTRANSFER => 1,
      CURLOPT_URL => $feedURL,
    ]);
    $resp = curl_exec($curl);
    $respStatus = curl_getinfo($curl, CURLINFO_HTTP_CODE);
    curl_close($curl);

    $events = [];
    if ($respStatus === 200) :
      $resp = json_decode($resp);
      if (isset($resp->events)) :
        $events = $resp->events;
      endif;
    endif;

    $val .=
      '<div class="ua_component_wrapper ua_layout--flow' . $className . $alignClass . '">
        <h2 class="' . $textAlignmentValue . '">' . $attributes['eventsHeading'] . '</h2>';

        if (count($events)) :
          $val .=
          '<ul class="ua_layout--grid "'.$columnStyles.'>';
      
            foreach ($events as $event) {
              $stamp = 0;
              if($event->event->event_instances[0]->event_instance->start) {
                $stamp = strtotime($event->event->event_instances[0]->event_instance->start);
              }

              $endStamp = 0;
              if($event->event->event_instances[0]->event_instance->end) {
                $endStamp = strtotime($event->event->event_instances[0]->event_instance->end);
              }

              $month = date('F', $stamp);
              $monthNum = date('n', $stamp);
              $monthNumZeros = date('m', $stamp);
              $dayNum = date('j', $stamp);
              $dayNumZeros = date('d', $stamp);
              $day = date('D', $stamp);

              $time = '';
              $mer = '';
              if($stamp) {
                $time = date('g:i', $stamp);
                $mer = date('a', $stamp);
                $mer = update_ap_style($mer);
              }

              $endTime = '';
              $endMer = '';
              if($endStamp){
                $endTime = date('g:i', $endStamp);
                $endMer = date('a', $endStamp);
                $endMer = update_ap_style($endMer);
              }



              $time24 = date('G:i:s', $stamp);
              $year = date('Y', $stamp);
              $experience = $event->event->experience;
              $allDay = $event->event->event_instances[0]->event_instance->all_day;
              $locationRoom = $event->event->room_number;
              $locationName = $event->event->location_name;
              $description = $event->event->description_text;
              $url = $event->event->localist_url;

              if ($time . $mer === '12:00am' || $time === '0:00') {
                $time = '';
              }

              $val .=
              '<li>
                <div class="ua_component_wrapper">
                  <article class="ua_event">
                    <h3 class="ua_event_name">
                      <a href="' . $url . '">' . $event->event->title . '</a>
                    </h3>

                    <p class="ua_event_date">
                      <span class="ua_event_month">' . $month . ' </span>
                      <span class="ua_event_date">' . $dayNum . '</span>
                    </p>';

                    if ($allDay) :
                      $val .=
                        '<p class="ua_event_time">
                          <span class="fa fa-clock" title="Time" aria-hidden="true"></span><span class="ua_visually-hidden">Time</span>&nbsp;All Day
                        </p>';
                    endif;

                    if (!$allDay && $time != '') :
                      $val .=
                        '<p class="ua_event_time">
                          <span class="fa fa-clock" title="Time" aria-hidden="true"></span>
                          <span class="ua_visually-hidden">Time</span>&nbsp;'
                          . $time . $mer;

                          if ($endStamp) {
                            $val .= ' - ' . $endTime . $endMer;
                          }
                        '</p>';
                    endif;

                    if ($experience === 'virtual') :
                      $val .=
                        '<p class="ua_event_location">
                          <span class="fa fa-location-dot" title="Location" aria-hidden="true"></span><span class="ua_visually-hidden">Location</span>&nbsp;Virtual Event
                        </p>';
                    endif;

                    if (!(($locationName == '') && ($locationRoom == ''))) :
                      if ($experience !== 'virtual') :
                        $val .=
                          '<p class="ua_event_location">
                            <span class="fa fa-location-dot" title="Location" aria-hidden="true"></span><span class="ua_visually-hidden">Location</span>&nbsp;';
                            if ($locationRoom != '') :
                              $val .= $locationRoom . ', ';
                            endif;
                            $val .= $locationName .
                          '</p>';
                      endif;
                    endif;

                    $val .=
                  '</article>
                </div>
              </li>';

              if ($count >= $limiter) {
                break;
              }
              $count++;
            }
            $val .=
          '</ul>';
        else :
          $val .= '<p>There are no upcoming events in this category.</p>';
        endif;

        $val .=
        '<div class="' . $textAlignmentValue . '"><a class="ua_cta" href="' . $calendarURL . '">Calendar</a></div>
      </section>';
  else :
    $val .=
      '<section class="event-feed">
        <h2>Events Feed</h2>
        <p>
          The events feed block relies on a PHP library called
          <a href="https://www.php.net/manual/en/book.curl.php">cURL</a> to retrieve
          the events data. cURL is not currently enabled on this server.
          Please contact your systems administrator to see about getting cURL
          installed and activated.
        </p>
      </section>';
  endif;

  $val .= '</div>';

  return $val;
}