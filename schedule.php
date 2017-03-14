<?php
// Fucking seriously. What is this, the 90s?
ini_set("auto_detect_line_endings", "1");

// - The first row should be the meeting names.
// - The second row is the discussion lead/owner.
// - The third row is the stakeholder count.
$m_csv = file('./meetings.csv');
const M_HEADER_ROWS = 3;

// - The first column is the name.
// - The second column is if they are a stakeholder in the sheet.
// - The third column is if they've been emailed yet.
// - The fourth column is if they've replied.
const M_HEADER_COLS = 5;

// Currently there are this many metadata rows before the timeslot row.
$a_csv = file('./availability.csv');
const A_PRE_HEADER_ROWS = 9;

// Get the meeting names from the first header row and remove it.
$meeting_names = array_shift($m_csv);
$meeting_names = explode(',', $meeting_names);

// Also remove additional header rows from the meeting list. Start from 1 since
// we already removed the first one.
for ($i = 1; $i < M_HEADER_ROWS; $i++) {
  array_shift($m_csv);
}

// Remove the pre-header rows from the availability sheet.
for ($i = 0; $i < A_PRE_HEADER_ROWS; $i++) {
  array_shift($a_csv);
}

// str_getcsv() doesn't work for some reason.
// Closures because I can.
array_walk($m_csv, function(&$row) {
  $row = explode(',', $row);
});
array_walk($a_csv, function(&$row) {
  $row = explode(',', $row);
});

// Get the names of the timeslots and remove the first three name columns.
$timeslots = array_shift($a_csv);
array_shift($timeslots);
array_shift($timeslots);
array_shift($timeslots);

// Assemble an associative array of the stakeholders for each meeting.
$meetings = array();
foreach ($meeting_names as $i => $name) {
  // Skip the header columns and empty columns; preserve the numerical indices.
  if ($i < M_HEADER_COLS || empty(trim($name))) {
    continue;
  }
  $meetings[trim($name)] = array();
  $meeting_names[$i] = trim($name);
}
foreach ($m_csv as $attendee_row) {
  // Get the stakeholder name.
  $attendee_row[0] = $attendee = trim(strtolower($attendee_row[0]));
  foreach ($attendee_row as $i => $cell) {
    // Skip header and empty columns, but preserve the numerical indices.
    if ($i < M_HEADER_COLS ||  empty($meeting_names[$i])) {
      continue;
    }
    // If there's a '1' in the cell, the attendee should attend that meeting.
    if (trim($cell) === '1') {
      $meetings[$meeting_names[$i]][] = $attendee;
    }
  }
}

// Make an associative array of the availability keyed by attendee.
$availability = array();

foreach ($a_csv as $attendee_row) {
  // The first column in the sprint spreadsheet template is real name. Who
  // uses real names? ;)
  array_shift($attendee_row);
  // The nick is in the second column.
  $attendee = strtolower(trim(array_shift($attendee_row)));
  // Some rows in the sprint spreadsheet are empty or header rows.
  if (!empty($attendee) && (empty($availability[$attendee]))) {
    $availability[$attendee] = $attendee_row;
  }
}

$no_reply = array();

// Now, determine which meetings can be held when.
foreach ($meetings as $meeting_name => $meeting) {
  print "\n*************************\nPossible times for $meeting_name:\n";
  $all_available = array();
  $some_missing = array();
  $dries = FALSE;
  foreach ($timeslots as $i => $timeslot) {
    $missing = $maybe = $remote = array();
    foreach ($meeting as $attendee) {
      if ($attendee == 'dries') {
        $dries = TRUE;
      }
      elseif (empty($availability[$attendee])) {
        $no_reply[] = $attendee;
      }
      elseif ((trim($availability[$attendee][$i]) == FALSE) || stripos($availability[$attendee][$i], 'no') !== FALSE) {
        $missing[] = $attendee;
      }
      elseif (stripos(trim($availability[$attendee][$i]), 'maybe') !== FALSE) {
        $maybe[] = $attendee;
      }
      elseif (stripos(trim($availability[$attendee][$i]), 'remote') !== FALSE) {
        $remote[] = $attendee;
      }
    }
    // If everyone is available, propose the slot.
    if (empty($missing) && empty($remote) && empty($maybe)) {
      $all_available[] = trim($timeslot);
    }
    // Otherwise, if only some people are available, add it as a fallback.
    elseif (sizeof($missing) < (sizeof($meeting)/3)) {
      $string = "\n" . trim($timeslot);
      if (!empty($missing)) {
        $string .= "  " . implode(" ", $missing) . " missing";
      }
      if (!empty($maybe)) {
        $string .= "\n(" . implode(" ", $maybe) . " maybe)";
      }
      if (!empty($remote)) {
        $string .= "\n(" . implode(" ", $remote) . " remote)";
      }

      $some_missing[] = $string;
    }
  }
  if ($dries) {
    print "***CHECK DRIES***\n";
  }
  if (!empty($all_available)) {
    print "*******\nAll: "  . implode(', ', $all_available) . "\n*******\n";
  }
  print implode("\n", $some_missing);
  print "\n\n";
}

print "\n*************************\n";

print "NO REPLIES FROM:";

print "\n";
$no_reply = array_unique($no_reply);
sort($no_reply);
print_r($no_reply);

print "\n\n";
