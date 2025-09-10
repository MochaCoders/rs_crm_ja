<script setup>
import { ref } from 'vue';
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import NavLink from '@/Components/NavLink.vue';
import { Link } from '@inertiajs/vue3';

const sidebarOpen = ref(false);
</script>

<template>
  <div class="flex min-h-screen bg-blue-100">
    <!-- Sidebar -->
    <aside
      :class="[
        'bg-blue-600 text-white w-64 flex flex-col transition-transform duration-300',
        sidebarOpen ? 'translate-x-0' : '-translate-x-64 sm:translate-x-0'
      ]"
    >
      <!-- Logo -->
      <div class="flex items-center justify-center h-16 border-b border-blue-700">
        <Link :href="route('dashboard.index')">
          <ApplicationLogo class="block w-auto h-10 text-white fill-current" />
        </Link>
      </div>

      <!-- Navigation Links (vertically aligned) -->
      <nav class="flex flex-col flex-1">
        <NavLink
          :href="route('dashboard.index')"
          :active="route().current('dashboard.index')"
          class="block px-5 py-5 rounded hover:bg-blue-700"
        >
          Dashboard
        </NavLink>
        <NavLink
          :href="route('properties.index')"
          :active="route().current('properties.index')"
          class="block px-5 py-5 rounded hover:bg-blue-700"
        >
          Properties
        </NavLink>
        <NavLink
          :href="route('email-templates.index')"
          :active="route().current('email-templates.index')"
          class="block px-5 py-5 rounded hover:bg-blue-700"
        >
          Email Templates
        </NavLink>
      </nav>
    </aside>

    <!-- Main Content -->
    <div class="flex flex-col flex-1">
      <!-- Top Bar -->
      <div class="flex items-center justify-between px-4 py-3 bg-blue-600 shadow">
        <!-- Left side: Mobile toggle + header slot -->
        <div class="flex items-center space-x-4">
          <!-- Mobile toggle button -->
          <button
            @click="sidebarOpen = !sidebarOpen"
            class="text-white sm:hidden focus:outline-none"
          >
            <svg
              class="w-6 h-6"
              fill="none"
              stroke="currentColor"
              viewBox="0 0 24 24"
            >
              <path
                v-if="!sidebarOpen"
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M4 6h16M4 12h16M4 18h16"
              />
              <path
                v-else
                stroke-linecap="round"
                stroke-linejoin="round"
                stroke-width="2"
                d="M6 18L18 6M6 6l12 12"
              />
            </svg>
          </button>

          <!-- Page Header Slot -->
          <div v-if="$slots.header" class="text-lg font-semibold text-white">
            <slot name="header" />
          </div>
        </div>

        <!-- Right side: User Dropdown -->
        <Dropdown align="right" width="48">
          <template #trigger>
            <button
              type="button"
              class="inline-flex items-center px-3 py-2 text-sm font-medium leading-4 text-white transition duration-150 ease-in-out border border-white rounded-md hover:text-blue-300 focus:outline-none"
            >
              {{ $page.props.auth.user.first_name }} {{ $page.props.auth.user.last_name }}
              <svg
                class="w-4 h-4 ml-2"
                xmlns="http://www.w3.org/2000/svg"
                fill="currentColor"
                viewBox="0 0 20 20"
              >
                <path
                  fill-rule="evenodd"
                  d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                  clip-rule="evenodd"
                />
              </svg>
            </button>
          </template>

          <template #content>
            <DropdownLink :href="route('profile.edit')">Profile</DropdownLink>
            <DropdownLink :href="route('logout')" method="post" as="button">
              Log Out
            </DropdownLink>
          </template>
        </Dropdown>
      </div>

      <!-- Page Content -->
      <main class="flex-1 p-6">
        <slot />
      </main>
    </div>
  </div>
</template>
