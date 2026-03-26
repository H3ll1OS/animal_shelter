<template>
    <AppLayout>
        <div class="adopt-page">
            <section class="adopt-hero adopt-hero--calm">
                <h1>Adoptable Animals</h1>
                <p>Welcome to our gallery of wonderful animals waiting for a loving home. Use the filters below to meet your potential new family member. Each one has a unique story and is ready to start a new chapter with you.</p>
            </section>

            <section class="filters filters-elevated">
                <div class="filter-container container">
                    <div class="filter-group filter-group--pills">
                        <label for="filter-species">Animal Type</label>
                        <div class="pill-group" id="species-pills">
                            <button type="button" class="pill-button" :class="{ active: filters.species === 'all' }" @click="updateFilter('species', 'all')">All</button>
                            <button type="button" class="pill-button" :class="{ active: filters.species === 'dog' }" @click="updateFilter('species', 'dog')">Dogs</button>
                            <button type="button" class="pill-button" :class="{ active: filters.species === 'cat' }" @click="updateFilter('species', 'cat')">Cats</button>
                        </div>
                    </div>

                    <div class="filter-group">
                        <label for="filter-age">Age</label>
                        <select id="filter-age" v-model="filters.age" @change="applyFilters">
                            <option value="all">All Ages</option>
                            <option value="baby">Baby (0-1 yr)</option>
                            <option value="young">Young (1-3 yrs)</option>
                            <option value="adult">Adult (3-8 yrs)</option>
                            <option value="senior">Senior (8+ yrs)</option>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label for="filter-size">Size</label>
                        <select id="filter-size" v-model="filters.size" @change="applyFilters">
                            <option value="all">All Size</option>
                            <option value="small">Small</option>
                            <option value="medium">Medium</option>
                            <option value="large">Large</option>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label for="filter-gender">Gender</label>
                        <select id="filter-gender" v-model="filters.gender" @change="applyFilters">
                            <option value="all">All</option>
                            <option value="male">Male</option>
                            <option value="female">Female</option>
                        </select>
                    </div>

                    <div class="filter-group">
                        <label for="sort-by">Sort By</label>
                        <select id="sort-by" v-model="filters.sort" @change="applyFilters">
                            <option value="name_asc">Name (A-Z)</option>
                            <option value="name_desc">Name (Z-A)</option>
                            <option value="age_asc">Age (Youngest First)</option>
                            <option value="age_desc">Age (Oldest First)</option>
                        </select>
                    </div>
                </div>
            </section>

            <section class="animals-gallery">
                <div id="adoption-gallery" class="animals-grid">
                    <div v-if="pets.data.length === 0" class="no-results">
                        <p>We currently don't have any animals available for adoption. Check back soon!</p>
                    </div>
                    <div v-else v-for="pet in pets.data" :key="pet.id" 
                         class="animal-card clickable-card" 
                         :data-id="pet.id"
                         :data-species="pet.species"
                         :data-age="pet.age"
                         :data-size="pet.size"
                         :data-gender="pet.gender"
                         @click="viewPet(pet.id)">
                        <div class="animal-card__media">
                            <img :src="'/storage/' + pet.image_path" :alt="pet.name">
                            <span class="animal-card__badge">{{ pet.species }}</span>
                        </div>
                        <div class="animal-info">
                            <h3>{{ pet.name }}</h3>
                            <p>{{ pet.breed || 'Mixed Breed' }}</p>
                        </div>
                    </div>
                </div>
            </section>

            <div class="custom-pagination-controls" v-if="pets.last_page > 1">
                <div class="pagination-nav">
                    <Link :href="pets.prev_page_url" class="nav-btn nav-btn-prev" v-if="pets.current_page > 1">
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="15,18 9,12 15,6"></polyline>
                        </svg>
                        Previous
                    </Link>
                    <button class="nav-btn nav-btn-disabled" disabled v-else>
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="15,18 9,12 15,6"></polyline>
                        </svg>
                        Previous
                    </button>

                    <div class="page-info">
                        <span>Page {{ pets.current_page }} of {{ pets.last_page }}</span>
                        <span class="page-count">({{ pets.total }} animals)</span>
                    </div>

                    <Link :href="pets.next_page_url" class="nav-btn nav-btn-next" v-if="pets.current_page < pets.last_page">
                        Next
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="9,18 15,12 9,6"></polyline>
                        </svg>
                    </Link>
                    <button class="nav-btn nav-btn-disabled" disabled v-else>
                        Next
                        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                            <polyline points="9,18 15,12 9,6"></polyline>
                        </svg>
                    </button>
                </div>
            </div>
        </div>
    </AppLayout>
</template>

<script setup>
import { Link } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    pets: {
        type: Object,
        required: true
    },
    filters: {
        type: Object,
        default: () => ({
            species: 'all',
            age: 'all',
            size: 'all',
            gender: 'all',
            sort: 'name_asc'
        })
    }
});

function updateFilter(key, value) {
    router.get('/adopt', {
        ...props.filters,
        [key]: value,
        page: 1
    }, { replace: true });
}

function applyFilters() {
    router.get('/adopt', {
        ...props.filters,
        page: 1
    }, { replace: true });
}

function viewPet(id) {
    window.location.href = `/adopt/${id}`;
}
</script>

