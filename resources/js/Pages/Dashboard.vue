<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import Modal from "@/Components/Modal.vue"; // Use your modal
import { Head, useForm } from "@inertiajs/vue3";
import { ref, onMounted, nextTick } from "vue";
import axios from "axios";

// Props
const { dogs, likedDogs: initialLikedDogs } = defineProps({
  dogs: {
    type: Array,
    default: () => [],
  },
  likedDogs: {
    type: Array,
    default: () => [],
  },
});

// Reactive States
const likedDogs = ref([...initialLikedDogs]);
const breedImages = ref({});
const isModalOpen = ref(false);
const isAlertModalOpen = ref(false);
const modalBreed = ref("");
const modalUsers = ref([]);
const alertMessage = ref("");

// Toggle Like Function
const toggleLike = (dog) => {
  if (likedDogs.value.includes(dog)) {
    likedDogs.value = likedDogs.value.filter((likedDog) => likedDog !== dog);

    syncSelections();
  } else if (likedDogs.value.length < 3) {
    likedDogs.value.push(dog);
    syncSelections();
  } else {
    alertMessage.value = "You can only like up to 3 dogs.";
    isAlertModalOpen.value = true;
  }
};

// Sync Liked Dogs with Backend
const syncSelections = () => {
  const form = useForm({
    dogs: likedDogs.value,
  });

  form.post(route("dogs.save"), {
    onSuccess: () => console.log("Selections synced successfully"),
    onError: (error) => console.error("Failed to sync selections:", error),
  });
};

// Fetch Image for a Breed
const fetchImage = async (dog) => {
  if (!breedImages.value[dog]) {
    try {
      const response = await axios.get(`/dogs/image/${dog}`);
      breedImages.value[dog] = response.data.image;
    } catch (error) {
      console.error(`Failed to fetch image for ${dog}:`, error);
    }
  }
};

// Lazy Load Images with IntersectionObserver
const observeImages = () => {
  const observer = new IntersectionObserver((entries) => {
    entries.forEach((entry) => {
      if (entry.isIntersecting) {
        const dog = entry.target.dataset.dog;
        fetchImage(dog);
        observer.unobserve(entry.target); // Stop observing once loaded
      }
    });
  });

  document.querySelectorAll(".lazy-image").forEach((el) => observer.observe(el));
};

// Fetch Users Who Liked a Breed
const fetchLikes = async (breed) => {
  try {
    const response = await axios.get(`/dogs/${breed}/likes`);
    modalBreed.value = breed;
    modalUsers.value = response.data.users;
    isModalOpen.value = true; // Open modal
  } catch (error) {
    console.error(`Failed to fetch likes for ${breed}:`, error);
  }
};

const closeModal = () => {
  isModalOpen.value = false; // Close modal
};

const closeAlertModal = () => {
  isAlertModalOpen.value = false;
};


// Initialize Observer on Mounted
onMounted(async () => {
  await nextTick(); // Ensure DOM is fully rendered before observing
  observeImages();
});
</script>
<template>
  <Head title="Dashboard" />

  <AuthenticatedLayout>
    <template #header>
      <h2 class="font-semibold text-xl text-gray-800 leading-tight">Dashboard</h2>
    </template>

    <div class="py-12">
      <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
          <!-- Dog Cards -->
          <div
            v-for="dog in dogs"
            :key="dog"
            class="border border-gray-200 shadow-lg rounded-lg overflow-hidden hover:shadow-xl transition-shadow duration-300"
          >
            <!-- Image Section -->
            <div class="relative">
              <!-- Placeholder While Loading -->
              <div
                v-if="!breedImages[dog]"
                class="lazy-image h-48 w-full bg-gray-200 flex items-center justify-center"
                :data-dog="dog"
              >
                <span>Loading...</span>
              </div>

              <!-- Loaded Image -->
              <img
                v-else
                :src="breedImages[dog]"
                :alt="dog"
                class="h-48 w-full object-cover rounded-lg"
              />

              <!-- Like Icon -->
              <div class="absolute top-2 right-2 flex flex-col items-center">
                <button
                  @click="toggleLike(dog)"
                  class="bg-white p-2 rounded-full shadow-md hover:shadow-lg"
                >
                  <svg
                    xmlns="http://www.w3.org/2000/svg"
                    fill="currentColor"
                    viewBox="0 0 24 24"
                    :class="{
                      'text-red-500': likedDogs.includes(dog),
                      'text-gray-400': !likedDogs.includes(dog),
                    }"
                    class="h-6 w-6"
                  >
                    <path
                      d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"
                    />
                  </svg>
                </button>
                <button
                  @click="fetchLikes(dog)"
                  class="text-xs font-medium mt-1 text-white hover:underline"
                >
                  Likes
                </button>
              </div>
            </div>

            <!-- Dog Name -->
            <div class="p-4">
              <h2 class="text-center text-lg font-semibold capitalize">
                {{ dog }}
              </h2>
            </div>
          </div>
        </div>
      </div>
    </div>

    <!-- Modal -->
    <Modal :show="isModalOpen" @close="closeModal">
  <template #default>
    <div class="p-6">
      <!-- Header -->
      <div class="flex items-center justify-between border-b pb-4">
        <h2 class="text-xl font-semibold text-gray-800">
          Likes for {{ modalBreed }}
        </h2>
        <button @click="closeModal" class="text-gray-500 hover:text-gray-700">
          <svg
            xmlns="http://www.w3.org/2000/svg"
            class="h-6 w-6"
            fill="none"
            viewBox="0 0 24 24"
            stroke="currentColor"
          >
            <path
              stroke-linecap="round"
              stroke-linejoin="round"
              stroke-width="2"
              d="M6 18L18 6M6 6l12 12"
            />
          </svg>
        </button>
      </div>

      <!-- Content -->
      <div class="mt-4">
        <p class="text-sm text-gray-600">
          Below is the list of users who liked {{ modalBreed }}:
        </p>
        <ul class="mt-6 space-y-3">
          <li
            v-for="user in modalUsers"
            :key="user.id"
            class="flex items-center gap-4 p-3 bg-gray-100 rounded-lg"
          >
            <div class="text-gray-800 font-medium">{{ user }}</div>
          </li>
        </ul>
      </div>
    </div>
  </template>
</Modal>

<!-- Alert Modal -->
<Modal :show="isAlertModalOpen" @close="closeAlertModal">
      <template #default>
        <div class="p-6">
          <h2 class="text-xl font-semibold text-red-500">Alert</h2>
          <p class="mt-4 text-gray-600">{{ alertMessage }}</p>
          <div class="mt-6 flex justify-end">
            <button
              @click="closeAlertModal"
              class="bg-red-500 text-white px-4 py-2 rounded-lg hover:bg-red-600"
            >
              Close
            </button>
          </div>
        </div>
      </template>
    </Modal>

  </AuthenticatedLayout>
</template>
