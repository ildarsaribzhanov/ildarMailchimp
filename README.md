# IldarMailchimp php

#### php class for work with MailChimp API v3.0


Examples

```php

// get lists recipients
$lists = $chimp->getLists($list_id);

...
$segment_name = 'email for Gold and Silver';
$segment_cond = array(
    'match'      => 'any',
    'conditions' => array(
        array(
            'condition_type' => 'TextMerge',
            'field'          => 'LNAME',
            'op'             => 'contains',
            'value'          => 'Gold'
        ),
        array(
            'condition_type' => 'TextMerge',
            'field'          => 'LNAME',
            'op'             => 'contains',
            'value'          => 'Silver'
        )
    )
);



// Create recipients segment
$new_segment = $chimp->createSegment($segment_name, $list_id, $segment_cond);
$new_segment_id = $new_segment->id;

// Create camping for
$camping = $chimp->createCamping($list_id, $email_subject, $from_name, $reply_email, $new_segment_id);


// Add camping content
$res = $chimp->createCampingContent($plain, $html, $camping);

// Send camping
$res = $chimp->sendCamping($camp_id);

```
