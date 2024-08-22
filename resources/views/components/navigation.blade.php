<flux:sidebar sticky stashable class="bg-zinc-50 dark:bg-zinc-900 border-r border-zinc-200 dark:border-zinc-700">
    <flux:sidebar.toggle class="lg:hidden" icon="x-mark" />

    <flux:brand href="#" logo="{{ Vite::asset('resources/images/logo/logo.svg') }}" name="Shard" class="px-2" />

    <flux:navlist variant="outline">
        <flux:navlist.item icon="home" href="{{ route('home') }}" :current="request()->routeIs('home')">Home</flux:navlist.item>
        <flux:navlist.item icon="rectangle-stack" href="#" badge="INOP">Queue</flux:navlist.item>
        <flux:navlist.item icon="rectangle-group" href="#" badge="INOP">Sets</flux:navlist.item>
        <flux:navlist.item icon="document-text" href="#" badge="INOP">Library</flux:navlist.item>
        <flux:navlist.item icon="user-circle" href="{{ route('people') }}" :current="request()->routeIs('people')">People</flux:navlist.item>
        <flux:navlist.item icon="check-circle" href="#" badge="INOP">Absences</flux:navlist.item>
        <flux:navlist.item icon="paper-airplane" href="#" badge="INOP">Holidays</flux:navlist.item>
        <flux:navlist.item icon="hashtag" href="{{ route('collections') }}" :current="request()->routeIs('collections')">Collections</flux:navlist.item>
        <flux:navlist.item icon="magnifying-glass" href="#" badge="INOP">Search</flux:navlist.item>

        <flux:navlist.group heading="Event History" expandable>
            <flux:navlist.item icon="star" href="#" badge="INOP">Events</flux:navlist.item>
            <flux:navlist.item icon="bell-alert" href="#" badge="INOP">Alerts</flux:navlist.item>
            <flux:navlist.item icon="arrow-turn-down-right" href="#" badge="INOP">Normal</flux:navlist.item>
            <flux:navlist.item icon="information-circle" href="#" badge="INOP">Recall</flux:navlist.item>
            <flux:navlist.item icon="tag" href="#" badge="INOP">System</flux:navlist.item>
        </flux:navlist.group>

        <flux:navlist.group heading="Time Tracking" expandable>
            <flux:navlist.item icon="clock" href="#" badge="INOP">Sessions</flux:navlist.item>
            <flux:navlist.item icon="exclamation-triangle" href="#" badge="INOP">Overtime</flux:navlist.item>
            <flux:navlist.item icon="arrow-down-circle" href="#" badge="INOP">Claimed</flux:navlist.item>
        </flux:navlist.group>
    </flux:navlist>

    <flux:navlist.group heading="Collections" expandable>
        <flux:navlist.item icon="hashtag" href="#" badge="INOP">Example</flux:navlist.item>
        <flux:navlist.item icon="hashtag" href="#" badge="INOP">Example</flux:navlist.item>
        <flux:navlist.item icon="hashtag" href="#" badge="INOP">Example</flux:navlist.item>
    </flux:navlist.group>

    <flux:spacer/>

    <flux:navlist variant="outline">
        <flux:navlist.item icon="cog-6-tooth" href="{{ route('profile') }}">Settings</flux:navlist.item>
        <livewire:partials.logout />
    </flux:navlist>
</flux:sidebar>
