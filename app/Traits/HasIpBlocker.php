<?php
namespace App\Traits;

use Illuminate\Support\Facades\Cache;

trait HasIpBlocker
{

     private function getLoginAttempts($ipAddress)
     {
         return intval(Cache::get("login_attempts:$ipAddress", 0));
     }
     private function incrementLoginAttempts($ipAddress)
     {
         $loginAttempts = $this->getLoginAttempts($ipAddress) + 1;
         Cache::put("login_attempts:$ipAddress", $loginAttempts, now()->addMinutes(15));
         if ($loginAttempts >= 5) {
             Cache::put("blocked_ip:$ipAddress", true, now()->addMinutes(15));
         }
     }
     private function isBlockedIP($ipAddress)
     {
         return Cache::has("blocked_ip:$ipAddress");
     }
 
     private function canAttemptLogin($ipAddress)
     {
         return !$this->isBlockedIP($ipAddress) && $this->getLoginAttempts($ipAddress) < 5;
     }
 
}