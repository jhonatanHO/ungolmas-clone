# Canonical Vue 3 SFC Pattern (`<script setup lang="ts">`)

This reference document provides a complete, production-ready example of the Combativa frontend architecture standard.

```vue
<template>
    <div class="user-card" :class="{ 'is-loading': isSubmitting }">
        <img :src="avatarUrl" :alt="userName" class="user-card__avatar" />
        <div class="user-card__info">
            <h4 class="user-card__name">{{ userName }}</h4>
            <p class="user-card__status">{{ formattedStatus }}</p>
        </div>

        <div class="user-card__actions">
            <button
                type="button"
                class="button button--primary"
                :disabled="isDisabled"
                @click="handleRequestFriend"
            >
                Add Friend
            </button>

            <!-- Dropdown menu -->
            <div class="dropdown">
                <button
                    type="button"
                    class="dropdown__trigger"
                    aria-label="More options"
                    :disabled="isDisabled"
                    @click="handleToggleDropdown"
                >
                    <i class="icon-more-vertical"></i>
                </button>
                <div v-show="isDropdownOpen" class="dropdown__menu">
                    <button
                        type="button"
                        class="dropdown__item dropdown__item--danger"
                        @click="handleRemoveFriend"
                    >
                        Remove Friend
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>

<script setup lang="ts">
import { ref, computed } from 'vue';
import axios from 'axios';
import nprogress from 'nprogress';

/* PROPS & EMITS */
interface Props {
    friendUuid: string;
    userName: string;
    avatarUrl: string;
    isFriend?: boolean;
}

const props = withDefaults(defineProps<Props>(), {
    isFriend: false,
});

const emit = defineEmits<{
    (e: 'friend-added', uuid: string): void;
    (e: 'friend-removed', uuid: string): void;
}>();

/* STATE */
const isSubmitting = ref<boolean>(false);
const isDropdownOpen = ref<boolean>(false);

/* COMPUTED */
const isDisabled = computed<boolean>(() => isSubmitting.value);
const formattedStatus = computed<string>(() => (props.isFriend ? 'Friend' : 'Suggested'));

/* API SERVICES */

/**
 * Sends a friend request to the API.
 *
 * @param {string} friendUuid - The UUID of the user to send the request to.
 * @param {() => void} [callBackSuccess] - Callback executed upon successful API response.
 * @returns {Promise<void>}
 */
const callBackFriendRequest = async (
    friendUuid: string,
    callBackSuccess?: () => void
): Promise<void> => {
    try {
        await axios.post('/api/friends/requests', { friend_uuid: friendUuid });
        if (callBackSuccess) callBackSuccess();
    } catch (error) {
        console.error('Failed to send friend request:', error);
    } finally {
        isSubmitting.value = false;
        nprogress.done();
    }
};

/**
 * Sends a request to remove a friend from the API.
 *
 * @param {string} friendUuid - The UUID of the friend to remove.
 * @param {() => void} [callBackSuccess] - Callback executed upon successful removal.
 * @returns {Promise<void>}
 */
const callBackFriendRemove = async (
    friendUuid: string,
    callBackSuccess?: () => void
): Promise<void> => {
    try {
        await axios.delete(`/api/friends/${friendUuid}`);
        if (callBackSuccess) callBackSuccess();
    } catch (error) {
        console.error('Failed to remove friend:', error);
    } finally {
        isSubmitting.value = false;
        nprogress.done();
    }
};

/* UI HANDLERS */

/**
 * Toggles the visibility of the actions dropdown menu.
 *
 * @returns {void}
 */
const handleToggleDropdown = (): void => {
    isDropdownOpen.value = !isDropdownOpen.value;
};

/**
 * Handles the friend request click event and dispatches the API callback.
 *
 * @returns {void}
 */
const handleRequestFriend = (): void => {
    isSubmitting.value = true;
    nprogress.start();

    const callBackSuccess = (): void => {
        emit('friend-added', props.friendUuid);
    };

    callBackFriendRequest(props.friendUuid, callBackSuccess);
};

/**
 * Handles the friend removal click event and dispatches the API callback.
 *
 * @returns {void}
 */
const handleRemoveFriend = (): void => {
    isDropdownOpen.value = false;
    isSubmitting.value = true;
    nprogress.start();

    const callBackSuccess = (): void => {
        emit('friend-removed', props.friendUuid);
    };

    callBackFriendRemove(props.friendUuid, callBackSuccess);
};
</script>
```
