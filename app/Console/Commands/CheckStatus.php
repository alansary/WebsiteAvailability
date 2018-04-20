<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Mail;
use App\User;
use App\Url;
use App\DownLog;
use App\Http\Helpers\Utilities;
use Carbon\Carbon as Carbon;

class CheckStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'CheckStatus {--id|userId= : The id of the user} {--uid|urlId= : The id of the url}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Checks all registered urls and save their status in the database. Also notify user by mail if any of their websites/apps is down or if any of their websites/apps is up after down with the down duration.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        // check if the user id is given
        if ($this->option('userId')) {
            // try to find the user
            try {
                $user = User::findOrFail($this->option('userId'));
            } catch (\Exception $e) {
                $this->error('User not found');
            }

            // check user urls and update their statuses
            $urls = $user->urls;
            $messages = collect();
            foreach ($urls as $url) {
                $message = $this->checkUrl($url);
                if ($message) {
                    $messages->push($message);
                }
            }

            // Send status mail if website is down or website was down then becomes up
            if (count($messages)) {
                if ($this->sendReport($user, $messages)) {
                    $this->info('Report sent successfully to '.$user->username);
                } else {
                    $this->error('Error occured while sending the report to '.$user->username);
                }
            } else {
                $this->info('No updates available');
            }
        } elseif ($this->option('urlId')) {
            // check if the url id is given
            // try to find the url
            try {
                $url = Url::findOrFail($this->option('urlId'));
            } catch (\Exception $e) {
                $this->error('URL not found');
            }

            // check the url and update its status
            $messages = collect();
            $message = $this->checkUrl($url);
            if ($message) {
                $messages->push($message);
            }

            // Send status mail if website is down or website was down then becomes up
            if (count($messages)) {
                if ($this->sendReport($user, $messages)) {
                    $this->info('Report sent successfully to '.$user->username);
                } else {
                    $this->error('Error occured while sending the report to '.$user->username);
                }
            } else {
                $this->info('No updates available');
            }
        } else {
            // check all the urls in the system
            $users = User::all();
            foreach ($users as $user) {
                // check user urls and update their statuses
                $urls = $user->urls;
                $messages = collect();
                foreach ($urls as $url) {
                    $message = $this->checkUrl($url);
                    if ($message) {
                        $messages->push($message);
                    }
                }

                // Send status mail if website is down or website was down then becomes up
                $success_mails = collect();
                $failure_mails = collect();
                if (count($messages)) {
                    if ($this->sendReport($user, $messages)) {
                        $success_mails->push('Report sent successfully to '.$user->username);
                    } else {
                        $failure_mails->push('Error occured while sending the report to '.$user->username);
                    }
                }
            }
            $this->table(['Errors'], $failure_mails);
            $this->table(['Success'], $success_mails);
        }
    }

    /**
     * function to check if a URL is up or down and return the message or null
     *
     * @param  Url $url URL to check if up or down
     * @return string           returns the message or null
     */
    protected function checkUrl(Url $url) {
        $message = null;
        // update the status of the URL
        $is_active = Utilities::curlInit($url->url);

        // if the url was down and then becomes up, send email to user with the down time and the current status
        // up after down
        if (!$url->is_active && $is_active) {
            $current_time = Carbon::now();
            $down_time = Carbon::createFromFormat('Y-m-d H:i:s', DownLog::where('url_id', '=', $url->id)->get()->first()->created_at);
            // calculate the down time
            $diff_in_seconds = $current_time->diffInSeconds($down_time);
            $num_minutes = $diff_in_seconds / 60;
            $num_seconds = $diff_in_seconds % 60;
            $num_hours = $num_minutes / 60;
            $num_minutes = $num_minutes % 70;
            $num_days = $num_hours / 24;
            $num_hours = $num_hours % 24;
            $num_weeks = $num_days / 7;
            $num_days = $num_days % 7;
            // fix the problem of math approximation
            if ($num_minutes < 1) {
                $num_minutes = 0;
            }
            if ($num_hours < 1) {
                $num_hours = 0;
            }
            if ($num_days < 1) {
                $num_days = 0;
            }
            if ($num_weeks < 1) {
                $num_weeks = 0;
            }
            $messages = [
                'message' => 'The url/app "'.$url->url.'" is now up, it was down for '.$num_weeks.' week(s), '.$num_days.' day(s), '.$num_hours.' hour(s), '.$num_minutes.' minute(s) and '.$num_seconds.' second.',
                'type' => true
            ];
            // deleting the down log
            DownLog::where('url_id', '=', $url->id)->delete();
        } elseif ($url->is_active && ! $is_active) {
            // if the url was up and then becomes down, create a down log
            // $messages->push('The url/app "'$url->url.'" was up and now is down.');
            DownLog::create([
                'url_id' => $url->id
            ]);
        } elseif ($url->is_active && $is_active) {
            // if the url is still up
            // $messages->push('The url/app "'$url->url.'" is still up.');
        } elseif (! $url->is_active && ! $is_active) {
            // if the url is still down
            $current_time = Carbon::now();
            $down_time = Carbon::createFromFormat('Y-m-d H:i:s', DownLog::where('url_id', '=', $url->id)->get()->first()->created_at);
            $diff_in_seconds = $current_time->diffInSeconds($down_time);
            // calculate the down time
            $num_minutes = $diff_in_seconds / 60;
            $num_seconds = $diff_in_seconds % 60;
            $num_hours = $num_minutes / 60;
            $num_minutes = $num_minutes % 70;
            $num_days = $num_hours / 24;
            $num_hours = $num_hours % 24;
            $num_weeks = $num_days / 7;
            $num_days = $num_days % 7;
            // fix the problem of math approximation
            if ($num_minutes < 1) {
                $num_minutes = 0;
            }
            if ($num_hours < 1) {
                $num_hours = 0;
            }
            if ($num_days < 1) {
                $num_days = 0;
            }
            if ($num_weeks < 1) {
                $num_weeks = 0;
            }
            $message = [
                'message' => 'The url/app "'.$url->url.'" is still down, it is down since '.$num_weeks.' week(s), '.$num_days.' day(s), '.$num_hours.' hour(s), '.$num_minutes.' minute(s) and '.$num_seconds.' second.',
                'type' => false
            ];
        }

        // updating the url status
        $url->is_active = ($is_active) ? 1 : 0;
        $url->save();

        return $message;
    }

    /**
     * function to send a report to the user
     *
     * @param  User $user user to send the report to
     * @param  array $messages array of messages
     * @return bool           returns the status of the message
     */
    protected function sendReport(User $user, $messages) {
        $data = ['user' => $user, 'messages' => $messages];
        Mail::send(['html'=>'emails.userURLReport'], $data, function($message) use ($user) {
         $message
            ->to($user->email, $user->username)
            ->subject('Website Availability Status Report');
         $message
            ->from('mohamed_alansary@rocketmail.com',config('app.name'));
        });

        // check for failures
        if (Mail::failures()) {
            return false;
        }
        return true;
    }
}
