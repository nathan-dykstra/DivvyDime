<x-app-layout>
    <x-slot name="header">
        <div class="btn-container-apart">
            <h2>Friends</h2>
            <div class="btn-container-end">
                @if (session('status') === 'invite-sent')
                    <p
                        x-data="{ show: true }"
                        x-show="show"
                        x-transition
                        x-init="setTimeout(() => show = false, 2000)"
                        class="text-success"
                    >{{ __('Friend request sent.') }}</p>
                @endif
                
                <x-primary-button x-data="" x-on:click.prevent="$dispatch('open-modal', 'send-friend-invite')" icon="fa-solid fa-user-plus icon">{{ __('Add Friend') }}</x-primary-button>
            </div>
        </div>
    </x-slot>

    <div class="friends-list-container">
        <table class="friends-table">
            <thead>
                <tr>
                    <th scope="col">#</th>
                    <th scope="col">Name</th>
                    <th scope="col">Email</th>
                </tr>
            </thead>
            <tbody>
                @foreach($friends as $friend)
                    <tr>
                        <th scope="row">{{$loop->iteration}}</th>
                        <td>{{$friend->username}}</td>
                        <td>{{$friend->email}}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <x-modal name="send-friend-invite" :show="$errors->friendInvite->isNotEmpty()" focusable>
        <form method="post" action="{{ route('friends.invite') }}" class="space-bottom-sm">
            @csrf

            <div>
                <h3>{{ __('Send a friend request') }}</h3>
                <p class="text-shy">
                    {{ __('If a user with this email address already exists, they\'ll recieve a notification in the "Activiy" section with your friend request. Otherwise, we\'ll send an email inviting them to the app.') }}
                </p>
                <p class="text-shy">
                    {{ __('We\'ll never share your email address with anyone.') }}
                </p>
            </div>

            <div>
                <x-input-label for="friend_email" value="{{ __('Password') }}" class="screen-reader-only" />
                <x-text-input id="friend_email" name="friend_email" type="email" placeholder="{{ __('Email') }}" required />
                <x-input-error :messages="$errors->userDeletion->get('friend_email')" />
            </div>

            <div class="btn-container-end">
                <x-secondary-button x-on:click="$dispatch('close')">{{ __('Cancel') }}</x-secondary-button>
                <x-primary-button type="submit" class="primary-color-btn">{{ __('Send Request') }}</x-danger-button>
            </div>
        </form>
    </x-modal>
</x-app-layout>

<style>
    /* Temporary friends table styles */
    .friends-table {
        border-collapse: collapse;
        width: 100%;
    }
  
    .friends-table td, .friends-table th {
        border: 1px solid var(--border-grey);
        padding: 8px;
        color: var(--text-primary);
    }
      
    .friends-table th {
        padding-top: 12px;
        padding-bottom: 12px;
        text-align: left;
        background-color: var(--secondary-grey);
    }


</style>