<script setup>
import AuthenticatedLayout from "@/Layouts/AuthenticatedLayout.vue";
import { Head, useForm } from "@inertiajs/vue3";
import { ref, onMounted } from 'vue';
import axios from 'axios';

// Define props
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

const likedDogs = ref([...initialLikedDogs]);

// Toggle like function
const toggleLike = (breed) => {
    if (likedDogs.value.includes(breed)) {
        likedDogs.value = likedDogs.value.filter((dog) => dog !== breed);

        unSelections();
    } else if (likedDogs.value.length < 3) {
        likedDogs.value.push(breed);

        saveSelections();
    } else {
        alert('You can only select up to 3 dogs!');
    }
};

// Save liked dogs to the backend
const saveSelections = () => {
  const form = useForm({
    dogs: likedDogs.value,
  });

  form.post(route("dogs.save"), {
    onSuccess: () => {

    },
    onError: () => {

    },
  });
};

const unSelections = () => {
  const form = useForm({
    dogs: likedDogs.value,
  });

  form.post(route("dogs.unsave"), {
    onSuccess: () => {

    },
    onError: () => {

    },
  });
}


// Function to fetch image for a breed
const fetchImage = async (breed) => {
    if (!breedImages.value[breed]) {
        try {
            const response = await axios.get(`/dogs/image/${breed}`);
            breedImages.value[breed] = response.data.image;
        } catch (error) {
            console.error(`Failed to fetch image for ${breed}:`, error);
        }
    }
};

// Observer for lazy loading
const observeImages = () => {
    const observer = new IntersectionObserver((entries) => {
        entries.forEach((entry) => {
            if (entry.isIntersecting) {
                const breed = entry.target.dataset.breed;
                fetchImage(breed);
                observer.unobserve(entry.target); // Stop observing once loaded
            }
        });
    });

    document.querySelectorAll('.lazy-image').forEach((el) => observer.observe(el));
};

// Initialize observer on mounted
onMounted(() => {
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
          <div
            v-for="dog in dogs"
            :key="dog.breed"
            class="border border-gray-200 shadow-lg rounded-lg overflow-hidden hover:shadow-xl transition-shadow duration-300"
          >
            <!-- Dog Image -->
            <div class="relative">
              <img :src="dog.image" :alt="dog.breed" class="h-48 w-full object-cover" />
              <!-- Like Icon -->
              <button
                @click="toggleLike(dog.breed)"
                class="absolute top-2 right-2 bg-white p-2 rounded-full shadow-md hover:shadow-lg"
              >
                <svg
                  xmlns="http://www.w3.org/2000/svg"
                  fill="currentColor"
                  viewBox="0 0 24 24"
                  :class="{
                    'text-red-500': likedDogs.includes(dog.breed),
                    'text-gray-400': !likedDogs.includes(dog.breed),
                  }"
                  class="h-6 w-6"
                >
                  <path
                    d="M12 21.35l-1.45-1.32C5.4 15.36 2 12.28 2 8.5 2 5.42 4.42 3 7.5 3c1.74 0 3.41.81 4.5 2.09C13.09 3.81 14.76 3 16.5 3 19.58 3 22 5.42 22 8.5c0 3.78-3.4 6.86-8.55 11.54L12 21.35z"
                  />
                </svg>
              </button>
            </div>

            <!-- Dog Breed -->
            <div class="p-4">
              <h2 class="text-center text-lg font-semibold capitalize">
                {{ dog.breed }}
              </h2>
            </div>
          </div>
        </div>
        <div class="mt-8 text-center">
          <button
            @click="saveSelections"
            class="bg-green-500 text-white px-6 py-2 rounded-lg hover:bg-green-600"
          >
            Save My Selection
          </button>
        </div>
      </div>
    </div>
  </AuthenticatedLayout>
</template>
