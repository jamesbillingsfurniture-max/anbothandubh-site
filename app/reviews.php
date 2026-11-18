<?php
// Guest reviews shown on the home page: 3 picked at random on each visit.
// To add one: copy a line, keep the quote marks and the comma at the end. First names only.
// Update the rating numbers when they change on Airbnb / Booking.com.
return [
    'ratings' => [
        'airbnb'  => ['score' => '4.97', 'count' => 34, 'extra' => 'Superhost'],
        'booking' => ['score' => '9.5', 'count' => 17, 'label' => 'Exceptional'],
    ],
    'reviews' => [
        ['The location was beautiful, so peaceful. The cabin was spotlessly clean and lovely decor. The host made us feel so welcome with a nice hand written note and fresh lemon water on arrival!', 'Caroline', 'United Kingdom', 'Booking.com'],
        ['The cabin was extremely cosy and had fabulous facilities! The shower was amazing, the bed was very very comfy. The decking is a beautiful spot for coffee in the morning.', 'Ryan', 'United Kingdom', 'Booking.com'],
        ['It was a lovely spot, quiet and serene. We loved Sadie! The decor was great, good selection of books.', 'Kirstine', 'Australia', 'Booking.com'],
    ],
];
