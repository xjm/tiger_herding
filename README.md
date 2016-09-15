# tiger_herding

## Usage

1. Fill out the Hard Problem stakeholder spreadsheet for the event, with attendees in rows and meetings in columns.
2. Have stakeholders fill out the sprint attendance spreadsheet (copy from the [sprint template](https://docs.google.com/spreadsheets/d/1glIXozp3GHj23bYAQMufqs1mQ-xnRZCDLurwZfmoGXM/edit)).
3. Remove any unnecessary commas or linebreaks from cells of the two spreadsheets. These can interfere with parsing.
4. Ensure that participants have entered their d.o username exactly in the signup sheet.
5. Export the Hard Problems stakeholder spreadsheet as `meetings.csv` and the sprint spreadsheet as `availability.csv` using Google Docs' default  CSV export.
6. Clean up any remaining commas or linebreaks within cells. For the sprint spreadsheet saved in `availability.csv`, you may just wish to delete the leading rows that contain instructions for the sheet.
7. Run `php schedule.php`.
