SharURL
=======

SharURL is a fully functional custom PHP/MySQL application for file sharing. A user can sign up for a free or paid plan. Paid signups are redirected to PayPal Standard for payment. Once payment goes through, the user is redirected back to the site. In the background, a PayPal postback is received and the user's account is upgraded to the purchased plan. Users can upload new files to their account and delete existing files. Their upload limit is tracked and enforced. Apache logs files are parsed to record download bandwidth consumption to the database and enforce user account bandwidth limits.

When I started working on this project, the HTML5 File API was not yet implemented in browsers, so file uploads are handled with swfUpload (Adobe Flash & JavaScript). It allows you to upload multiple files at once and show upload progress. When a download is requested for the first time, files are automatically zipped up (without compression for very fast zipping).

This project involved a year of sporadic development, finally launching in July 2009. Unfortunately, there were lots of competitors out there by then (Dropbox included) and I had little interest in doing the marketing and competing at that point. As a result, the service acquired a tiny user base and was very lightly used by those few users over the years.

Though I wouldn't call the venture a success, [the project was a learning experience](http://bradt.ca/archives/developing-sharurl-lessons-learned-developing-a-new-startup/). There's plenty of code here that could be better, but there are some nice little nuggets as well. Hopefully someone finds them useful.
