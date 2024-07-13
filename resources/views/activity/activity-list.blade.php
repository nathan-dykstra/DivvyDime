<x-app-layout>
    <!-- Title & Header -->

    <x-slot name="title">
        {{ __('Activity') }}
    </x-slot>

    <x-slot name="header_title">
        {{ __('Activity') }}
    </x-slot>

    <!-- Content -->

    <div class="section-search">
        <div class="btn-container-start">
            <x-dropdown align="left">
                <x-slot name="trigger">
                    <x-primary-button class="expense-round-btn" id="activity-filter" icon="fa-solid fa-filter icon">{{ __('Filter') }}</x-primary-button>
                </x-slot>
    
                <x-slot name="content">
                    <div class="dropdown-item" onclick="filterNotifications('requires-action')">
                        <i class="fa-solid fa-circle-exclamation"></i>
                        <div>{{ __('Requires Action') }}</div>
                    </div>
                    <div class="dropdown-item" onclick="filterNotifications('expenses')">
                        <i class="fa-solid fa-receipt"></i>
                        <div>{{ __('Expenses') }}</div>
                    </div>
                    <div class="dropdown-item" onclick="filterNotifications('payments')">
                        <i class="fa-solid fa-scale-balanced"></i>
                        <div>{{ __('Payments') }}</div>
                    </div>
                    <div class="dropdown-divider"></div>
                    <div class="dropdown-item" onclick="filterNotifications('reset')">
                        <i class="fa-solid fa-rotate-left"></i>
                        <div>{{ __('Reset') }}</div>
                    </div>
                </x-slot>
            </x-dropdown>

            <x-primary-button class="expense-round-btn" icon="fa-solid fa-broom icon" onclick="clearAllNotifications()">{{ __('Clear All') }}</x-primary-button>
        </div>
    </div>

    <div class="activity-list-container">
        <!-- No notifications message -->
        <div class="notifications-empty-container hidden" id="no-activity">
            <div class="notifications-empty-icon"><i class="fa-solid fa-bell-slash"></i></div>
            <div class="notifications-empty-text">{{ __('No activity!') }}</div>
        </div>
        
        <div class="notifications" id="activity-list"></div>
        <!--include('activity.partials.notifications')-->

        <!-- Loading animation -->
        <div id="activity-loading">
            <x-list-loading></x-list-loading>
        </div>
    </div>
</x-app-layout>

<script>
    // Global constants and variables

    let page = 1;
    let loading = false;
    let lastPage = false;
    let filter = '';

    function acceptFriendRequest(notificationId) {
        $.ajax({
            url: '/friends/requests/'+ notificationId + '/accept',
            method: 'POST',
            data: {
                '_token': '{{ csrf_token() }}',
            },
            success: function(response) {
                fetchNotifications(filter, true);
            },
            error: function(error) {
                console.error(error);
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
                removeNotificationElement(notificationElement);

                setTimeout(() => {
                    fetchNotifications(filter, true);
                }, 400);
            },
            error: function(error) {
                console.error(error);
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
                fetchNotifications(filter, true);
            },
            error: function(error) {
                console.error(error);
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
                removeNotificationElement(notificationElement);

                setTimeout(() => {
                    fetchNotifications(filter, true);
                }, 400);
            },
            error: function(error) {
                console.error(error);
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
                fetchNotifications(filter, true);
                
            },
            error: function(error) {
                console.error(error);
            }
        })
    }

    function rejectPayment(event, notificationId) {
        event.stopPropagation();

        $.ajax({
            url: "{{ route('payments.reject') }}",
            method: 'POST',
            data: {
                '_token': '{{ csrf_token() }}',
                'notification_id': notificationId,
            },
            success: function(response) {
                fetchNotifications(filter, true);
            },
            error: function(error) {
                console.error(error);
            }
        })
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

                    //fetchNotifications(true); TODO
                }, 400);
            },
            error: function(error) {
                console.error(error);
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

                if (response.deletedNotificationIds.length == 0) return;

                // Clear each notification with cascading effect

                notifications.forEach(notification => {
                    if (response.deletedNotificationIds.includes(parseInt(notification.dataset.notificationId))) {
                        setTimeout(() => {
                            removeNotificationElement(notification);
                        }, delay);

                        delay += 50;
                    }
                });

                setTimeout(() => {
                    fetchNotifications(filter, true);
                }, 600);
            },
            error: function(error) {
                console.error(error);
            }
        })
    }

    function fetchNotifications(query, replace = false) {
        const loadingPlaceholder = document.getElementById('activity-loading');
        const noNotificationsMessage = document.getElementById('no-activity');
        const notificationsList = document.getElementById('activity-list');

        loading = true;

        if (replace) {
            notificationsList.innerHTML = '';
            lastPage = false;
            page = 1;
        }

        if (lastPage) {
            loading = false;
            return;
        }

        noNotificationsMessage.classList.add('hidden');
        loadingPlaceholder.classList.remove('hidden');

        $.ajax({
            url: '{{ route('activity.get-activity') }}' + '?page=' + page,
            method: 'GET',
            data: {
                'filter': query
            },
            success: function(response) {
                if (response.is_last_page) lastPage = true;
                page = parseInt(response.current_page) + 1;

                const html = response.html;

                setTimeout(() => {
                    loadingPlaceholder.classList.add('hidden');

                    if (replace) { // Replace the content on filter or page load
                        if (html.trim().length == 0) {
                            noNotificationsMessage.classList.remove('hidden');
                        } else {
                            noNotificationsMessage.classList.add('hidden');
                            notificationsList.innerHTML = html;
                        }
                    } else { // Append to the content on scroll
                        notificationsList.insertAdjacentHTML('beforeend', html); 
                    }
                }, replace ? 300 : 600);

                loading = false;
            },
            error: function(error) {
                loadingPlaceholder.classList.add('hidden');
                loading = false;
                console.error(error);
            }
        });
    }

    function filterNotifications(newFilter) {
        filter = newFilter;

        const filterTrigger = document.getElementById('activity-filter');

        switch (filter) {
            case 'requires-action':
                filterTrigger.classList.add('activity-filter-active');
                break;
            case 'expenses':
                filterTrigger.classList.add('activity-filter-active');
                break;
            case 'payments':
                filterTrigger.classList.add('activity-filter-active');
                break;
            case 'reset':
                filterTrigger.classList.remove('activity-filter-active');
                filter = '';
                break;
        }

        fetchNotifications(filter, true);
    }

    document.addEventListener('DOMContentLoaded', function() {
        fetchNotifications(filter, true);

        function handleScroll() {
            if (loading) return;

            if (window.scrollY + window.innerHeight >= document.documentElement.scrollHeight - 100) {
                fetchNotifications(filter);
            }
        }
        document.addEventListener('scroll', handleScroll);
    });
</script>
