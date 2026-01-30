@extends('statamic::layout')

@section('title', $title)

@section('content')
    <header class="flex flex-wrap items-center justify-between gap-4 px-2 sm:px-0 py-6 max-md:pb-8 md:py-8">
        <h1 class="text-[25px] leading-[1.25] st-text-legibility font-medium antialiased flex items-center gap-2.5 md:flex-1">
            <div class="size-5 relative">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="size-5 text-gray-500">
                    <path d="M12 22s8-4 8-10V5l-8-3-8 3v7c0 6 8 10 8 10z"/>
                    <path d="M9 12l2 2 4-4"/>
                </svg>
            </div>
            {{ $title }}
        </h1>
    </header>

    @if(session('success'))
        <div class="bg-green-500/10 border border-green-500/30 text-green-600 dark:text-green-400 px-4 py-3 rounded-xl mb-6">
            {{ session('success') }}
        </div>
    @endif

    <form action="{{ cp_route('mime-guard.update') }}" method="POST">
        @csrf

        {{-- Global Restrictions --}}
        <div class="bg-gray-100 dark:bg-gray-950/35 rounded-2xl p-1.5 mb-6">
            <div class="bg-white dark:bg-gray-850 rounded-xl ring ring-gray-200 dark:ring-gray-700/80 shadow-sm px-4 py-5">
                <header class="mb-6">
                    <h2 class="font-bold text-lg text-gray-925 dark:text-gray-300">{{ __('mime-guard::messages.global_restrictions') }}</h2>
                    <p class="text-sm text-gray-600/90 dark:text-gray-400 mt-1">{{ __('mime-guard::messages.global_restrictions_help') }}</p>
                </header>

                <div class="mb-4">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <label class="text-sm font-medium text-gray-925 dark:text-gray-300 block">{{ __('mime-guard::messages.restricted_mime_types') }}</label>
                            <p class="text-sm text-gray-600/90 dark:text-gray-400 mt-0.5">{{ __('mime-guard::messages.check_to_block') }}</p>
                        </div>
                        <label class="flex items-center gap-2 text-sm cursor-pointer bg-linear-to-b from-white to-gray-50 dark:from-gray-850 dark:to-gray-900 border border-gray-300 dark:border-gray-700/80 shadow-sm px-3 py-1.5 rounded-lg transition-colors hover:to-gray-100 dark:hover:to-gray-850">
                            <input type="checkbox" id="toggle-all-mime" class="form-checkbox">
                            <span class="text-gray-900 dark:text-gray-300">{{ __('mime-guard::messages.toggle_all') }}</span>
                        </label>
                    </div>

                    <div id="mime-checkboxes" class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-3 mb-4">
                        @foreach($commonMimeTypes as $category => $types)
                            <div class="border border-gray-200 dark:border-gray-700 rounded-xl p-4 bg-gray-50 dark:bg-gray-900 mime-category-card">
                                <button type="button" class="category-toggle font-medium text-sm text-gray-925 dark:text-gray-300 block mb-3 hover:text-blue-600 dark:hover:text-blue-400 cursor-pointer transition-colors">{{ $category }}</button>
                                @foreach($types as $mime => $label)
                                    <label class="flex items-center gap-2 text-sm py-1.5 cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-800 px-2 -mx-2 rounded-lg transition-colors">
                                        <input
                                            type="checkbox"
                                            name="restricted_by_default[]"
                                            value="{{ $mime }}"
                                            {{ in_array($mime, $settings['restricted_by_default'] ?? []) ? 'checked' : '' }}
                                            class="form-checkbox"
                                        >
                                        <span class="text-gray-700 dark:text-gray-400" title="{{ $mime }}">{{ $label }}</span>
                                    </label>
                                @endforeach
                            </div>
                        @endforeach
                    </div>

                    <div class="mt-6">
                        <label class="text-sm font-medium text-gray-925 dark:text-gray-300 mb-1.5 block">{{ __('mime-guard::messages.custom_mime_types') }}</label>
                        <p class="text-sm text-gray-600/90 dark:text-gray-400 mb-2">{{ __('mime-guard::messages.custom_mime_types_help') }}</p>
                        <textarea
                            name="restricted_by_default_custom"
                            class="w-full bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-700 text-gray-925 dark:text-gray-300 placeholder:text-gray-500 dark:placeholder:text-gray-400/85 shadow-sm rounded-lg px-3 py-2 font-mono text-sm"
                            rows="3"
                            placeholder="application/x-custom&#10;text/csv"
                        >{{ implode("\n", array_diff($settings['restricted_by_default'] ?? [], array_keys(array_merge(...array_values($commonMimeTypes))))) }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        {{-- Container Rules --}}
        <div class="bg-gray-100 dark:bg-gray-950/35 rounded-2xl p-1.5 mb-6">
            <div class="bg-white dark:bg-gray-850 rounded-xl ring ring-gray-200 dark:ring-gray-700/80 shadow-sm px-4 py-5">
                <header class="mb-6 flex items-start justify-between">
                    <div>
                        <h2 class="font-bold text-lg text-gray-925 dark:text-gray-300">{{ __('mime-guard::messages.container_rules') }}</h2>
                        <p class="text-sm text-gray-600/90 dark:text-gray-400 mt-1">{{ __('mime-guard::messages.container_rules_help') }}</p>
                    </div>
                    <a href="{{ cp_route('asset-containers.create') }}" class="inline-flex items-center justify-center font-medium bg-linear-to-b from-white to-gray-50 dark:from-gray-850 dark:to-gray-900 hover:to-gray-100 dark:hover:to-gray-850 text-gray-900 dark:text-gray-300 border border-gray-300 dark:border-gray-700/80 shadow-sm px-3 h-8 text-sm rounded-lg">
                        {{ __('mime-guard::messages.create_container') }}
                    </a>
                </header>

                @if(count($containers) > 0)
                    <div class="space-y-3">
                        @foreach($containers as $container)
                            @php
                                $containerRules = $settings['containers'][$container['handle']] ?? [];
                                $hasRules = !empty($containerRules['allow']);
                            @endphp
                            <div class="border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-900 collapsible-card" data-collapsed="true">
                                <button type="button" class="collapsible-header w-full flex items-center justify-between p-4 text-left hover:bg-gray-100 dark:hover:bg-gray-800 rounded-xl transition-colors">
                                    <h3 class="font-medium text-gray-925 dark:text-gray-300">
                                        <span class="text-gray-500 dark:text-gray-500">{{ $container['handle'] }}</span>
                                        <span class="text-gray-400 mx-1">&mdash;</span>
                                        <span>{{ $container['title'] }}</span>
                                        @if($hasRules)
                                            <span class="ml-2 text-xs bg-blue-100 dark:bg-blue-500/20 text-blue-700 dark:text-blue-400 px-2 py-0.5 rounded-full">{{ __('mime-guard::messages.configured') }}</span>
                                        @endif
                                    </h3>
                                    <svg class="collapsible-icon w-5 h-5 text-gray-400 dark:text-gray-500 transform transition-transform -rotate-90" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                    </svg>
                                </button>

                                <div class="collapsible-content hidden px-4 pb-4">
                                    <p class="text-sm text-gray-600/90 dark:text-gray-400 mb-3">{{ __('mime-guard::messages.container_types_help') }}</p>

                                    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-2 mb-3">
                                        @foreach($commonMimeTypes as $category => $types)
                                            <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-3 bg-white dark:bg-gray-800 mime-category-card">
                                                <button type="button" class="category-toggle font-medium text-xs text-gray-700 dark:text-gray-400 block mb-2 hover:text-blue-600 dark:hover:text-blue-400 cursor-pointer transition-colors">{{ $category }}</button>
                                                @foreach($types as $mime => $label)
                                                    <label class="flex items-center gap-2 text-xs py-1 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700 px-1 -mx-1 rounded transition-colors">
                                                        <input
                                                            type="checkbox"
                                                            name="containers[{{ $container['handle'] }}][allow][]"
                                                            value="{{ $mime }}"
                                                            {{ in_array($mime, $containerRules['allow'] ?? []) ? 'checked' : '' }}
                                                            class="form-checkbox"
                                                        >
                                                        <span class="text-gray-600 dark:text-gray-400" title="{{ $mime }}">{{ $label }}</span>
                                                    </label>
                                                @endforeach
                                            </div>
                                        @endforeach
                                    </div>

                                    <div>
                                        <label class="text-xs font-medium text-gray-700 dark:text-gray-400 mb-1 block">{{ __('mime-guard::messages.custom_mime_types') }}</label>
                                        <textarea
                                            name="containers[{{ $container['handle'] }}][allow_custom]"
                                            class="w-full bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-700 text-gray-925 dark:text-gray-300 placeholder:text-gray-500 dark:placeholder:text-gray-400/85 shadow-sm rounded-lg px-3 py-2 font-mono text-xs"
                                            rows="2"
                                            placeholder="custom/type"
                                        >{{ implode("\n", array_diff($containerRules['allow'] ?? [], array_keys(array_merge(...array_values($commonMimeTypes))))) }}</textarea>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-gray-500 dark:text-gray-400 italic">{{ __('mime-guard::messages.no_containers') }}</p>
                @endif
            </div>
        </div>

        {{-- Blueprint Rules --}}
        <div class="bg-gray-100 dark:bg-gray-950/35 rounded-2xl p-1.5 mb-6">
            <div class="bg-white dark:bg-gray-850 rounded-xl ring ring-gray-200 dark:ring-gray-700/80 shadow-sm px-4 py-5">
                <header class="mb-6">
                    <h2 class="font-bold text-lg text-gray-925 dark:text-gray-300">{{ __('mime-guard::messages.blueprint_rules') }}</h2>
                    <p class="text-sm text-gray-600/90 dark:text-gray-400 mt-1">{{ __('mime-guard::messages.blueprint_rules_help') }}</p>
                </header>

                @if(count($blueprints) > 0)
                    <div class="space-y-3">
                        @foreach($blueprints as $blueprint)
                            @php
                                $blueprintRules = $settings['blueprints'][$blueprint['handle']] ?? [];
                                $hasRules = !empty($blueprintRules['allow']);
                            @endphp
                            <div class="border border-gray-200 dark:border-gray-700 rounded-xl bg-gray-50 dark:bg-gray-900 collapsible-card" data-collapsed="true">
                                <button type="button" class="collapsible-header w-full flex items-center justify-between p-4 text-left hover:bg-gray-100 dark:hover:bg-gray-800 rounded-xl transition-colors">
                                    <h3 class="font-medium text-gray-925 dark:text-gray-300">
                                        <span>{{ $blueprint['title'] }}</span>
                                        <span class="text-gray-500 dark:text-gray-500 text-sm font-normal ml-2">{{ $blueprint['handle'] }}</span>
                                        @if($hasRules)
                                            <span class="ml-2 text-xs bg-blue-100 dark:bg-blue-500/20 text-blue-700 dark:text-blue-400 px-2 py-0.5 rounded-full">{{ __('mime-guard::messages.configured') }}</span>
                                        @endif
                                    </h3>
                                    <svg class="collapsible-icon w-5 h-5 text-gray-400 dark:text-gray-500 transform transition-transform -rotate-90" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path>
                                    </svg>
                                </button>

                                <div class="collapsible-content hidden px-4 pb-4">
                                    <p class="text-sm text-gray-600/90 dark:text-gray-400 mb-3">{{ __('mime-guard::messages.blueprint_types_help') }}</p>

                                    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-2 mb-3">
                                        @foreach($commonMimeTypes as $category => $types)
                                            <div class="border border-gray-200 dark:border-gray-700 rounded-lg p-3 bg-white dark:bg-gray-800 mime-category-card">
                                                <button type="button" class="category-toggle font-medium text-xs text-gray-700 dark:text-gray-400 block mb-2 hover:text-blue-600 dark:hover:text-blue-400 cursor-pointer transition-colors">{{ $category }}</button>
                                                @foreach($types as $mime => $label)
                                                    <label class="flex items-center gap-2 text-xs py-1 cursor-pointer hover:bg-gray-50 dark:hover:bg-gray-700 px-1 -mx-1 rounded transition-colors">
                                                        <input
                                                            type="checkbox"
                                                            name="blueprints[{{ $blueprint['handle'] }}][allow][]"
                                                            value="{{ $mime }}"
                                                            {{ in_array($mime, $blueprintRules['allow'] ?? []) ? 'checked' : '' }}
                                                            class="form-checkbox"
                                                        >
                                                        <span class="text-gray-600 dark:text-gray-400" title="{{ $mime }}">{{ $label }}</span>
                                                    </label>
                                                @endforeach
                                            </div>
                                        @endforeach
                                    </div>

                                    <div>
                                        <label class="text-xs font-medium text-gray-700 dark:text-gray-400 mb-1 block">{{ __('mime-guard::messages.custom_mime_types') }}</label>
                                        <textarea
                                            name="blueprints[{{ $blueprint['handle'] }}][allow_custom]"
                                            class="w-full bg-white dark:bg-gray-900 border border-gray-300 dark:border-gray-700 text-gray-925 dark:text-gray-300 placeholder:text-gray-500 dark:placeholder:text-gray-400/85 shadow-sm rounded-lg px-3 py-2 font-mono text-xs"
                                            rows="2"
                                            placeholder="custom/type"
                                        >{{ implode("\n", array_diff($blueprintRules['allow'] ?? [], array_keys(array_merge(...array_values($commonMimeTypes))))) }}</textarea>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-gray-500 dark:text-gray-400 italic">{{ __('mime-guard::messages.no_blueprints') }}</p>
                @endif
            </div>
        </div>

        {{-- Logging --}}
        <div class="bg-gray-100 dark:bg-gray-950/35 rounded-2xl p-1.5 mb-6">
            <div class="bg-white dark:bg-gray-850 rounded-xl ring ring-gray-200 dark:ring-gray-700/80 shadow-sm px-4 py-5">
                <header class="mb-4">
                    <h2 class="font-bold text-lg text-gray-925 dark:text-gray-300">{{ __('mime-guard::messages.logging') }}</h2>
                </header>

                <label class="flex items-center gap-3 cursor-pointer">
                    <input
                        type="checkbox"
                        name="logging_enabled"
                        value="1"
                        {{ ($settings['logging']['enabled'] ?? true) ? 'checked' : '' }}
                        class="form-checkbox"
                    >
                    <span class="text-gray-925 dark:text-gray-300">{{ __('mime-guard::messages.logging_enabled') }}</span>
                </label>
                <p class="text-sm text-gray-600/90 dark:text-gray-400 mt-2 ml-7">{{ __('mime-guard::messages.logging_help') }}</p>
            </div>
        </div>

        {{-- Submit --}}
        <div class="flex justify-end">
            <button type="submit" class="relative inline-flex items-center justify-center whitespace-nowrap shrink-0 font-medium antialiased cursor-pointer no-underline disabled:[&_svg]:opacity-30 disabled:cursor-not-allowed [&_svg]:shrink-0 dark:[&_svg]:text-white bg-linear-to-b from-primary/90 to-primary hover:bg-primary-hover text-white disabled:opacity-60 disabled:text-white dark:disabled:text-white border border-primary-border shadow-ui-md inset-shadow-2xs inset-shadow-white/25 disabled:inset-shadow-none dark:disabled:inset-shadow-none [&_svg]:text-white [&_svg]:opacity-60 px-4 h-10 text-sm gap-2 rounded-lg">
                {{ __('mime-guard::messages.save_settings') }}
            </button>
        </div>
    </form>

    {{-- Help Section --}}
    <div class="bg-gray-100 dark:bg-gray-950/35 rounded-2xl p-1.5 mt-6">
        <div class="bg-white dark:bg-gray-850 rounded-xl ring ring-gray-200 dark:ring-gray-700/80 shadow-sm px-4 py-5">
            <h3 class="font-bold text-gray-925 dark:text-gray-300 mb-3">{{ __('mime-guard::messages.help_title') }}</h3>
            <div class="text-sm space-y-3">
                <div>
                    <p class="font-medium text-gray-925 dark:text-gray-300">{{ __('mime-guard::messages.help_hierarchy') }}</p>
                    <p class="text-gray-600/90 dark:text-gray-400 mt-1">{{ __('mime-guard::messages.help_hierarchy_desc') }}</p>
                </div>
                <div>
                    <p class="font-medium text-gray-925 dark:text-gray-300">{{ __('mime-guard::messages.help_wildcards') }}</p>
                    <p class="text-gray-600/90 dark:text-gray-400 mt-1">{{ __('mime-guard::messages.help_wildcards_desc') }}</p>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Toggle all checkboxes (global restrictions)
            const toggleAll = document.getElementById('toggle-all-mime');
            const checkboxContainer = document.getElementById('mime-checkboxes');
            const checkboxes = checkboxContainer.querySelectorAll('input[type="checkbox"]');

            function updateToggleAllState() {
                const checkedCount = checkboxContainer.querySelectorAll('input[type="checkbox"]:checked').length;
                toggleAll.checked = checkedCount === checkboxes.length;
                toggleAll.indeterminate = checkedCount > 0 && checkedCount < checkboxes.length;
            }

            toggleAll.addEventListener('change', function() {
                checkboxes.forEach(cb => cb.checked = this.checked);
            });

            checkboxes.forEach(cb => {
                cb.addEventListener('change', updateToggleAllState);
            });

            updateToggleAllState();

            // Category title toggle (click on category name to toggle all in that category)
            document.querySelectorAll('.category-toggle').forEach(categoryTitle => {
                categoryTitle.addEventListener('click', function(e) {
                    e.preventDefault();
                    const card = this.closest('.mime-category-card');
                    if (!card) return;

                    const categoryCheckboxes = card.querySelectorAll('input[type="checkbox"]');
                    const allChecked = Array.from(categoryCheckboxes).every(cb => cb.checked);

                    categoryCheckboxes.forEach(cb => {
                        cb.checked = !allChecked;
                    });

                    // Update global toggle state if in global restrictions
                    if (checkboxContainer.contains(this)) {
                        updateToggleAllState();
                    }
                });
            });

            // Wildcard toggle (e.g., image/*, video/* toggles all in category)
            document.querySelectorAll('input[type="checkbox"][value$="/*"]').forEach(wildcardCheckbox => {
                wildcardCheckbox.addEventListener('change', function() {
                    const card = this.closest('.mime-category-card');
                    if (!card) return;

                    const categoryCheckboxes = card.querySelectorAll('input[type="checkbox"]');
                    categoryCheckboxes.forEach(cb => {
                        if (cb !== this) {
                            cb.checked = this.checked;
                        }
                    });

                    // Update global toggle state if in global restrictions
                    if (checkboxContainer.contains(this)) {
                        updateToggleAllState();
                    }
                });
            });

            // Update wildcard checkbox when all individual types are checked
            function updateWildcardState(card) {
                const wildcardCheckbox = card.querySelector('input[type="checkbox"][value$="/*"]');
                if (!wildcardCheckbox) return;

                const otherCheckboxes = Array.from(card.querySelectorAll('input[type="checkbox"]'))
                    .filter(cb => cb !== wildcardCheckbox);

                const allChecked = otherCheckboxes.every(cb => cb.checked);
                wildcardCheckbox.checked = allChecked;
            }

            // Listen for changes on non-wildcard checkboxes
            document.querySelectorAll('.mime-category-card input[type="checkbox"]:not([value$="/*"])').forEach(checkbox => {
                checkbox.addEventListener('change', function() {
                    const card = this.closest('.mime-category-card');
                    if (card) {
                        updateWildcardState(card);
                    }
                });
            });

            // Initialize wildcard states on page load
            document.querySelectorAll('.mime-category-card').forEach(card => {
                updateWildcardState(card);
            });

            // Collapsible cards
            document.querySelectorAll('.collapsible-card').forEach(card => {
                const header = card.querySelector('.collapsible-header');
                const content = card.querySelector('.collapsible-content');
                const icon = card.querySelector('.collapsible-icon');

                header.addEventListener('click', function() {
                    const isCollapsed = card.dataset.collapsed === 'true';

                    if (isCollapsed) {
                        content.classList.remove('hidden');
                        icon.classList.remove('-rotate-90');
                        icon.classList.add('rotate-0');
                        card.dataset.collapsed = 'false';
                    } else {
                        content.classList.add('hidden');
                        icon.classList.add('-rotate-90');
                        icon.classList.remove('rotate-0');
                        card.dataset.collapsed = 'true';
                    }
                });
            });
        });
    </script>
@endsection
