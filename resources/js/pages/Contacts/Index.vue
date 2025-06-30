<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed, watch } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Badge } from '@/components/ui/badge';
import { 
    Users, 
    UserPlus, 
    Search, 
    Filter, 
    Star,
    Phone,
    MessageSquare,
    Mail,
    MoreVertical,
    Edit,
    Trash2
} from 'lucide-vue-next';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuSeparator, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import ContactCard from '@/components/ContactCard.vue';
import type { BreadcrumbItem } from '@/types';

interface Contact {
    id: number;
    name: string;
    phone_number: string;
    formatted_phone: string;
    email?: string;
    notes?: string;
    is_favorite: boolean;
    tags?: string[];
    created_at: string;
    updated_at: string;
}

interface ContactsData {
    data: Contact[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    links: Array<{ url?: string; label: string; active: boolean }>;
}

interface Props {
    contacts: ContactsData;
    search?: string;
    filter?: string;
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Contacts',
        href: '/contacts',
    },
];

const searchQuery = ref(props.search || '');
const filterValue = ref(props.filter || 'all');

// Watch for changes and update URL
watch([searchQuery, filterValue], ([newSearch, newFilter]) => {
    const params: Record<string, string> = {};
    if (newSearch) params.search = newSearch;
    if (newFilter !== 'all') params.filter = newFilter;
    
    router.get(route('contacts.index'), params, {
        preserveState: true,
        replace: true,
    });
}, { debounce: 300 });

const handleToggleFavorite = async (contactId: number) => {
    try {
        await router.post(route('contacts.toggle-favorite', contactId), {}, {
            preserveState: true,
            preserveScroll: true,
        });
    } catch (error) {
        console.error('Failed to toggle favorite:', error);
    }
};

const handleDelete = (contactId: number) => {
    if (confirm('Are you sure you want to delete this contact?')) {
        router.delete(route('contacts.destroy', contactId));
    }
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Contacts" />

        <div class="space-y-6">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold tracking-tight">Contacts</h1>
                    <p class="text-muted-foreground">
                        Manage your contact directory for calls and SMS
                    </p>
                </div>
                <Button as-child>
                    <Link :href="route('contacts.create')">
                        <UserPlus class="mr-2 h-4 w-4" />
                        Add Contact
                    </Link>
                </Button>
            </div>

            <!-- Filters and Search -->
            <Card>
                <CardHeader>
                    <CardTitle class="flex items-center gap-2">
                        <Filter class="h-5 w-5" />
                        Filter & Search
                    </CardTitle>
                </CardHeader>
                <CardContent>
                    <div class="flex flex-col gap-4 md:flex-row md:items-end">
                        <div class="flex-1 space-y-2">
                            <Label for="search">Search contacts</Label>
                            <div class="relative">
                                <Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                                <Input
                                    id="search"
                                    v-model="searchQuery"
                                    placeholder="Search by name, phone, or email..."
                                    class="pl-10"
                                />
                            </div>
                        </div>
                        <div class="space-y-2">
                            <Label for="filter">Filter</Label>
                            <Select v-model="filterValue">
                                <SelectTrigger class="w-40">
                                    <SelectValue />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="all">All Contacts</SelectItem>
                                    <SelectItem value="favorites">Favorites</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Contact Stats -->
            <div class="grid gap-4 md:grid-cols-3">
                <Card>
                    <CardHeader class="pb-2">
                        <CardTitle class="text-sm font-medium">Total Contacts</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ props.contacts.total }}</div>
                    </CardContent>
                </Card>
                <Card>
                    <CardHeader class="pb-2">
                        <CardTitle class="text-sm font-medium">Favorites</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">
                            {{ props.contacts.data.filter(c => c.is_favorite).length }}
                        </div>
                    </CardContent>
                </Card>
                <Card>
                    <CardHeader class="pb-2">
                        <CardTitle class="text-sm font-medium">With Email</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">
                            {{ props.contacts.data.filter(c => c.email).length }}
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Contacts Grid -->
            <div v-if="props.contacts.data.length > 0" class="space-y-4">
                <div class="grid gap-4 md:grid-cols-2 lg:grid-cols-3">
                    <ContactCard
                        v-for="contact in props.contacts.data"
                        :key="contact.id"
                        :contact="contact"
                        @toggle-favorite="handleToggleFavorite"
                        @delete="handleDelete"
                    />
                </div>

                <!-- Pagination -->
                <div v-if="props.contacts.last_page > 1" class="flex items-center justify-center space-x-2">
                    <Button
                        v-for="link in props.contacts.links"
                        :key="link.label"
                        :variant="link.active ? 'default' : 'outline'"
                        size="sm"
                        :disabled="!link.url"
                        @click="link.url && router.get(link.url)"
                        v-html="link.label"
                    />
                </div>
            </div>

            <!-- Empty State -->
            <Card v-else class="text-center py-12">
                <CardContent>
                    <Users class="mx-auto h-12 w-12 text-muted-foreground mb-4" />
                    <h3 class="text-lg font-semibold mb-2">No contacts found</h3>
                    <p class="text-muted-foreground mb-6">
                        {{ searchQuery || filterValue !== 'all' 
                            ? 'Try adjusting your search or filter criteria' 
                            : 'Get started by adding your first contact' }}
                    </p>
                    <Button as-child v-if="!searchQuery && filterValue === 'all'">
                        <Link :href="route('contacts.create')">
                            <UserPlus class="mr-2 h-4 w-4" />
                            Add Your First Contact
                        </Link>
                    </Button>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
