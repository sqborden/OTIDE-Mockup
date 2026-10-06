<?php

function render_event($attributes) {
  $values = [
    'allDay'  => false,
    'displayTime' => true,
    'title' => '',
    'start' => 'now',
    'end' => 'now',
    'timezone' => 'America/Chicago',
    'url' => '',
    'location' => '',
    'opensInNewTab' => false,
    'className' => '',
  ];

  foreach( $values as $key => $value ) {
    if (array_key_exists($key, $attributes)) {
      $values[$key] = $attributes[$key];
    }
  }

  $start = new \DateTime($values['start']);
  $end = new \DateTime($values['end']);
  $start->setTimezone(new \DateTimeZone($values['timezone']));
  $end->setTimezone(new \DateTimeZone($values['timezone']));
  $startDate = $start->format('M d');
  $startTime = $start->format('g:i a');
  $startTime = str_replace('am','a.m.',$startTime);
  $startTime = str_replace('pm','p.m.',$startTime);
  $startYear = $start->format('Y');
  $endDate = $end->format('M d');
  $endTime = $end->format('g:i a');
  $endTime = str_replace('am','a.m.',$endTime);
  $endTime = str_replace('pm','p.m.',$endTime);
  $endYear = $end->format('Y');
  $year = ($startYear === $endYear) ? $startYear : null;

  $val =  
    '<div class="ua_component_wrapper '. $values['className'] .'">
      <article class="ua_event">
        <h3 class="ua_event_name">';
          if($values['opensInNewTab'] AND $values['url'] !== '') {
            $val .= '<a href="' . $values['url'] . '" target="_blank" rel="noreferrer noopener">' . $values['title'] .'</a>';
          } elseif($values['url'] !== '') {
            $val .= '<a href="' . $values['url'] . '">' . $values['title'] .'</a>';
          } else {
            $val .= $values['title'];
          } $val .=
        '</h3>
        <p class="ua_event_date">';
        $val.= 
          (!$end) ?
            ( '<span>' . $startDate . ' ' . $startTime . '</span>' ) :
            ( $year ? 
              ( 
                $startDate === $endDate ? 
                '<span>' . $startDate . '</span>'
                : '<span>' . $startDate . ' - ' . $endDate . '</span>' 
              )
            : ( '<span>' .  $startDate . ' ' . $startYear . ' - ' .  $endDate . ' ' . $endYear . '</span>' ));
        
        $val .= '</p>';
        
        if($values['displayTime']) {
          $val .= 
          '<p class="ua_event_time">
            <span class="fa fa-clock" title="Time" aria-hidden="true"></span>
            <span class="ua_visually-hidden">Time</span> ';

            if($values['allDay']) {
              $val .= 'All Day';
            } else {
              if($startTime == $endTime && $startDate == $endDate) {
                $val .= $startTime;   
              } else {
                $val .= $startTime . ' - ' . $endTime;
              }
            }

            $val .=
          '</p>';
        }

        if($values['location'] !== '') {
          $val .= 
            '<p class="ua_event_location">
              <span class="fa fa-location-dot" title="Location" aria-hidden="true"></span>
              <span class="ua_visually-hidden">Location</span>
              ' . $values['location'] . '
            </p>';
        } $val .=
      '</article>
    </div>';

  return $val;
}
