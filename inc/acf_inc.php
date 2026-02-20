<?php 
// ACF Helpers and filters
add_filter('acf/load_field/name=post_type', function ($field) {
  $field['choices'] = [];

  $pts = get_post_types(['public' => true], 'objects');

  foreach ($pts as $pt) {
    if (in_array($pt->name, ['attachment'], true)) continue;
    $field['choices'][$pt->name] = $pt->labels->singular_name;
  }

  return $field;
});