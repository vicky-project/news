<?php

return [
  'id' => 'news',
  'name' => 'NewsHub',
  'description' => 'Jelajahi berita terkini dari berbagai sumber terpercaya — dalam satu aplikasi ringan.',
  'icon_emoji' => '📰',
  'render_type' => 'iframe',
  'render_config' => [
    'url' => env('APP_URL') . '/apps/news'
  ]
];