# The Good Morrow
This is an island hotel like no other. Here you can become one with the island and prove John Donne wrong. The perfect site for the perfect hotel in fact. You can book rooms and order extras like poems and a music video.

This is a school project made by a student of the Yrgo Webdesign Class of 22. More info can be found at https://www.yrgopelago.se/

# Instructions
The site is live at http://www.garagehider.com/good-morrow/

There is an API that can be reached at http://www.garagehider.com/good-morrow/api/bookings.php
All info needed to use it can be read there or in the .json response from making a post request.

If you want to run a copy of this site you'll need to make your own .env file containing an API key for the yrgopelago central bank and also a link to a tasty music video of your own making. You'll also need to install several .php packages and run the site on a .php server.
# Code review

1. index.php:139,142,145 - Closing tag typo, “?>>” instead of “?>”.
2. booking.css:4 - Values of 0 shouldn’t have units specified.
3. calendar.css:63-64 - No rules are applied to the selector.
4. functions.js:3 - I think you forgot an extra “&” in “...(arrivalSelect.value != '') & (departureSelect.value != '')…”
5.  index.php:98 - Required “alt” attribute with alternative text is missing.
6.  index.php:120 - The “for” attribute for the tag needs to be equal to the “id” attribute of the related element. Id is missing.
7. index.php - Consider using the “main” tag to specify the main content of the page.
8. style.css - Consider using the property “cursor” with the value “pointer” for things such as buttons and links.  
9. calendar.css:5 - It’s recommended to have at least one fallback font, if there’s a problem with the main font.
10. index.php:31-33 - It would make more sense if the buttons that lets the user choose between the different hotel rooms were button or list item elements, instead of headings.
