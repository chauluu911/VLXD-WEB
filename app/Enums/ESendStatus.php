<?php
namespace App\Enums;


abstract class ESendStatus {
    const WAITING = 0;
    const SENT = 1;
    const FAILED = 2;
}
