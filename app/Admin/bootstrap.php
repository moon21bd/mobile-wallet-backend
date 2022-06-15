<?php

/**
 * Laravel-admin - admin builder based on Laravel.
 * @author z-song <https://github.com/z-song>
 *
 * Bootstraper for Admin.
 *
 * Here you can remove builtin form field:
 * Platform\Admin\Form::forget(['map', 'editor']);
 *
 * Or extend custom form field:
 * Platform\Admin\Form::extend('php', PHPEditor::class);
 *
 * Or require js and css assets:
 * Admin::css('/packages/prettydocs/css/styles.css');
 * Admin::js('/packages/prettydocs/js/main.js');
 *
 */

use App\Repositories\CoreHandler;
use Encore\Admin\Grid;
use Platform\Admin\Form;
use Platform\Admin\Facades\Admin;
use Encore\Admin\Form\Tools;

Admin::favicon('/images/favicon.ico');
Form::forget(['map']);
Form::extend('editor', Encore\Admin\Form\Field\Editor::class);

Grid::init(function (Grid $grid) {

    $grid->disableRowSelector();

    $grid->disableColumnSelector();

    $grid->actions(function ($action) {
        $action->disableView();
    });
});

Form::init(function ($form) {

    $form->disableEditingCheck();

    $form->disableCreatingCheck();

    $form->disableViewCheck();

    $form->tools(function (Tools $tools) {
        $tools->disableDelete();
        $tools->disableView();
        $tools->disableList();
    });
});

\Encore\Admin\Facades\Admin::navbar(function (\Encore\Admin\Widgets\Navbar $navbar) {
    if(!empty(Admin::user())) {
        if(Admin::user()->username != 'admin') {
            $userSubscriptionDetail = \App\Models\Platform\UserSubscription::with('service')->where('user_id', Admin::user()->id)->get();
            $CoreHandlerObj = new CoreHandler();
            foreach ($userSubscriptionDetail as $subscriptionDetail) {
                $result = $CoreHandlerObj->getBalance($subscriptionDetail->core_ref_id);
                $navbar->right('<li>
                <a href="#">
                   '.$subscriptionDetail->service->name.'
                   <span class="label label-info">'.number_format($result, 2).' Tk</span>
                </a>
            </li>');
            }
        }

        if ( (bool) config('platform-admin.enable_notification') == true) {
            if(!empty(Admin::user())) {
                $notificationHTML = '';
                $notificationCount = \App\Models\Notification::where('is_seen', 0)
                    ->where('user_id', Admin::user()->id)
                    ->count();
                $notifications = \App\Models\Notification::where('user_id', Admin::user()->id)
                    ->orderBy('id', 'desc')
                    ->get();

                foreach ($notifications as $notification) {
                    $timeElapsed = new \App\Repositories\TimeElapsed();

                    $notificationHTML .= '<li><!-- start message -->
                    <a href="'.$notification->link.'" class="notification-viewer" data-id="'.$notification->id.'">
                      <small><i class="fa fa-clock-o"></i> '.$timeElapsed->timeElapsedString($notification->created_at).'</small>
                      <h4>
                        '.$notification->title.'
                      </h4>
                      <p>'.$notification->description.'</p>
                    </a>
                  </li>';
                }

                $navbar->right('
                <li class="dropdown messages-menu">
                    <a href="#" id="notification-icon" class="dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                      <i class="fa fa-bell-o"></i>
                      <span class="label label-success notification-count" id="notification-counter">'.$notificationCount.'</span>
                    </a>
                    <ul class="dropdown-menu">
                      <li class="header">You have <span class="notification-count">'.$notificationCount.'</span> messages</li>
                      <li>
                        <!-- inner menu: contains the actual data -->
                        <ul class="menu" id="notification-message-area">
                            '.$notificationHTML.'
                        </ul>
                      </li>
                      <li class="footer"><a href="/admin/ext-platform-admin/notifications">See All Messages</a></li>
                    </ul>
                </li>
                ');
            }
        }
    }
});
if(!empty(Admin::user()) && (bool) config('platform-admin.enable_notification') == true) {
    Admin::script(
    //<<<JS
        "
        function makeid(length) {
            var result = '';
            var characters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz0123456789';
            var charactersLength = characters.length;
            for (var i = 0; i < length; i++) {
                result += characters.charAt(Math.floor(Math.random() * charactersLength));
            }
            return result;
        }

        let subscriptionID = 'user-' + '".Admin::user()->id."';
        let clientID = 'user-' + '".Admin::user()->id."' + '_' + makeid(6);

        console.log('clientID::', clientID);

        let client = new Paho.MQTT.Client('".config('platform-admin.mqtt_server')."', Number(".config('platform-admin.mqtt_client_port')."), clientID);

        let httpsEnableConfigValue = '".config('platform-admin.mqtt_https_enabled')."';
        let httpsEnable = (httpsEnableConfigValue == '1');

        // set callback handlers
        client.onConnectionLost = onConnectionLost;
        client.onMessageArrived = onMessageArrived;

        // connect the client
        client.connect({onSuccess: onConnect, useSSL: httpsEnable});

        // called when the client connects
        function onConnect() {
            // Once a connection has been made, make a subscription and send a message.
            console.log('Connected');
            client.subscribe(subscriptionID);
        }

        // called when the client loses its connection
        function onConnectionLost(responseObject) {
            if (responseObject.errorCode !== 0) {
                console.log('onConnectionLost:' + responseObject.errorMessage);
                client.connect({onSuccess: onConnect, useSSL: httpsEnable});
            }
        }

        function onMessageArrived(message) {
            let data = JSON.parse(message.payloadString);
            //console.log(data);
            var html = '';
            html += '<li>' +
             '<a href=\"'+ data['link'] +'\" class=\"notification-viewer\" data-id=\"'+ data['id'] +'\">' +
              '<small><i class=\"fa fa-clock-o\"></i> just now</small>' +
              '<h4>' +
                ''+ data['title'] +'' +
              '</h4>' +
              '<p>'+ data['description'] +'</p>' +
             '</a>' +
           '</li>';
           $('#notification-message-area').prepend( html );
                var notification_count = parseInt($('#notification-counter').text());
                //console.log('notification_count', notification_count);
                $('.notification-count').text(notification_count + 1);
            }
"
//JS
    );

    Admin::script(
        <<<JS

    $('#notification-icon').on('click', function() {
        console.log('clicked');
        var data = $(".notification-viewer").map(function() {
           return $(this).attr('data-id');
        }).get();
        console.log('data::', data.length);
        if (data.length > 0) {
            $.ajax({
                url: '/admin/ext-platform-admin/notification/status-update',
                type: "POST",
                data: {
                        'ids': data,
                         '_token': $('meta[name="csrf-token"]').attr('content')
                    },
                success: function(result) {
                    console.log('Result::', result);
                    if(result == 'success') {
                        setTimeout(function() {
                          $('.notification-count').text('0')
                        }, 1500);
                    }
                }
            });
        }
    });
JS
    );
}
