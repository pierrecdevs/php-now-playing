<?php

namespace Afh\Spotify\Models;

class Device
{
  public string $id;
  public bool $is_active;
  public bool $is_private_session;
  public bool $is_restricted;
  public string $name;
  public string $type;
  public int $volume_percent;
}
