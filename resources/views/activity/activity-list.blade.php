<x-app-layout>
    <x-slot name="header">
        <div class="btn-container-apart">
            <h2>{{ __('Activity') }}</h2>

            <div class="btn-container-end">
                <x-primary-button onclick="clearAllNotifications()">{{ __('Clear All') }}</x-primary-button>
            </div>
        </div>
    </x-slot>

    <div class="activity-list-container">
        @include('activity.partials.notifications')
    </div>
</x-app-layout>

<script>
    function refreshNotificationsView() {
        let updatedNotifications = $('.notifications');

        $.ajax({
            url: "{{ route('activity.get-updated-notifications') }}",
            method: 'GET',
            data: {
                '_token': '{{ csrf_token() }}',
            },
            success: function(html) {
                updatedNotifications.replaceWith(html);
            },
            error: function(error) {
                console.log(error);
            }
        });
    }

    function acceptFriendRequest(notificationId) {
        $.ajax({
            url: '/friends/requests/'+ notificationId + '/accept',
            method: 'POST',
            data: {
                '_token': '{{ csrf_token() }}',
            },
            success: function(response) {
                refreshNotificationsView();
            },
            error: function(error) {
                console.log(error);
            }
        })
    }

    function denyFriendRequest(denyBtn, notificationId) {
        $.ajax({
            url: '/friends/requests/'+ notificationId + '/deny',
            method: 'DELETE',
            data: {
                '_token': '{{ csrf_token() }}',
            },
            success: function(response) {
                notificationElement = denyBtn.closest('.notification');
                $(notificationElement).remove();
            },
            error: function(error) {
                console.log(error);
            }
        })
    }

    function acceptGroupInvite(notificationId, groupId) {
        $.ajax({
            url: "{{ route('groups.accept') }}",
            method: 'POST',
            data: {
                '_token': '{{ csrf_token() }}',
                'notification_id': notificationId,
                'group_id': groupId,
            },
            success: function(response) {
                refreshNotificationsView();
            },
            error: function(error) {
                console.log(error);
            }
        })
    }

    function rejectGroupInvite(rejectBtn, notificationId) {
        $.ajax({
            url: "{{ route('groups.reject') }}",
            method: 'DELETE',
            data: {
                '_token': '{{ csrf_token() }}',
                'notification_id': notificationId,
            },
            success: function(response) {
                notificationElement = rejectBtn.closest('.notification');
                $(notificationElement).remove();
            },
            error: function(error) {
                console.log(error);
            }
        })
    }

    function confirmPayment(event, notificationId) {
        event.stopPropagation();

        $.ajax({
            url: "{{ route('payments.confirm') }}",
            method: 'POST',
            data: {
                '_token': '{{ csrf_token() }}',
                'notification_id': notificationId,
            },
            success: function(response) {
                refreshNotificationsView();
            },
            error: function(error) {
                console.log(error);
            }
        })
    }

    function rejectPayment(event, rejectBtn, notificationId) {
        event.stopPropagation();

        // TODO
    }

    function removeNotificationElement(notificationElement) {
        notificationElement.classList.add('slide-out');
    }

    function deleteNotification(event, notificationId) {
        event.stopPropagation();

        $.ajax({
            url: '/activity/' + notificationId + '/delete',
            method: 'DELETE',
            data: {
                '_token': '{{ csrf_token() }}',
            },
            success: function(response) {
                let notificationElement = event.target.closest('.notification');
                let notifications = document.querySelectorAll('.notification');
                let indexToDelete = Array.from(notifications).indexOf(notificationElement);

                removeNotificationElement(notificationElement);

                setTimeout(() => {
                    notificationElement.remove();

                    // Translate notifications below the deleted notification up
                    for (let i = indexToDelete + 1; i < notifications.length; i++) {
                        notifications[i].style.transform = 'translateY(-' + notificationElement.offsetHeight + 'px)';
                        notifications[i].style.transform = '';
                    }
                }, 400);
            },
            error: function(error) {
                console.log(error);
            }
        })
    }

    function clearAllNotifications() {
        $.ajax({
            url: "{{ route('activity.clear-all') }}",
            method: 'DELETE',
            data: {
                '_token': '{{ csrf_token() }}',
            },
            success: function(response) {
                let notifications = document.querySelectorAll('.notification');

                let delay = 0;
                let notificationsDeleted = 0;

                // Clear each notification with cascading effect
                notifications.forEach(notification => {
                    if (response.deletedNotificationIds.includes(parseInt(notification.dataset.notificationId))) {
                        setTimeout(() => {
                            removeNotificationElement(notification);

                            notificationsDeleted++;

                            // After all notifications are cleared, refresh the view
                            if (notificationsDeleted === response.deletedNotificationIds.length) {
                                refreshNotificationsView();
                            }

                        }, delay);

                        delay += 50;
                    }
                });
            },
            error: function(error) {
                console.log(error);
            }
        })
    }
</script>
