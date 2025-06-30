<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { ref } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Switch } from '@/components/ui/switch';
import { Badge } from '@/components/ui/badge';
import InputError from '@/components/InputError.vue';
import { UserPlus, Tag, X } from 'lucide-vue-next';
import type { BreadcrumbItem } from '@/types';

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Contacts',
        href: '/contacts',
    },
    {
        title: 'Add Contact',
        href: '/contacts/create',
    },
];

const form = useForm({
    name: '',
    phone_number: '',
    email: '',
    notes: '',
    is_favorite: false,
    tags: [] as string[],
});

const newTag = ref('');

const addTag = () => {
    const tag = newTag.value.trim();
    if (tag && !form.tags.includes(tag)) {
        form.tags.push(tag);
        newTag.value = '';
    }
};

const removeTag = (index: number) => {
    form.tags.splice(index, 1);
};

const handleKeyPress = (event: KeyboardEvent) => {
    if (event.key === 'Enter') {
        event.preventDefault();
        addTag();
    }
};

const submit = () => {
    form.post(route('contacts.store'));
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head title="Add Contact" />

        <div class="max-w-2xl mx-auto space-y-6">
            <!-- Header -->
            <div>
                <h1 class="text-3xl font-bold tracking-tight">Add New Contact</h1>
                <p class="text-muted-foreground">
                    Create a new contact for calls and SMS messaging
                </p>
            </div>

            <!-- Form -->
            <Card>
                <CardHeader>
                    <CardTitle class="flex items-center gap-2">
                        <UserPlus class="h-5 w-5" />
                        Contact Information
                    </CardTitle>
                    <CardDescription>
                        Fill in the contact details below. Only name and phone number are required.
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <form @submit.prevent="submit" class="space-y-6">
                        <!-- Basic Information -->
                        <div class="space-y-4">
                            <div class="grid gap-4 md:grid-cols-2">
                                <div class="space-y-2">
                                    <Label for="name">Full Name *</Label>
                                    <Input
                                        id="name"
                                        v-model="form.name"
                                        placeholder="Enter full name"
                                        required
                                    />
                                    <InputError :message="form.errors.name" />
                                </div>

                                <div class="space-y-2">
                                    <Label for="phone_number">Phone Number *</Label>
                                    <Input
                                        id="phone_number"
                                        v-model="form.phone_number"
                                        placeholder="+1234567890"
                                        type="tel"
                                        class="font-mono"
                                        required
                                    />
                                    <InputError :message="form.errors.phone_number" />
                                </div>
                            </div>

                            <div class="space-y-2">
                                <Label for="email">Email Address</Label>
                                <Input
                                    id="email"
                                    v-model="form.email"
                                    placeholder="contact@example.com"
                                    type="email"
                                />
                                <InputError :message="form.errors.email" />
                            </div>

                            <div class="space-y-2">
                                <Label for="notes">Notes</Label>
                                <Textarea
                                    id="notes"
                                    v-model="form.notes"
                                    placeholder="Add any notes about this contact..."
                                    rows="3"
                                    class="resize-none"
                                />
                                <InputError :message="form.errors.notes" />
                            </div>
                        </div>

                        <!-- Tags -->
                        <div class="space-y-3">
                            <Label>Tags</Label>
                            <div class="flex flex-wrap gap-2 mb-2">
                                <Badge
                                    v-for="(tag, index) in form.tags"
                                    :key="index"
                                    variant="secondary"
                                    class="group"
                                >
                                    {{ tag }}
                                    <button
                                        type="button"
                                        @click="removeTag(index)"
                                        class="ml-1 hover:text-destructive"
                                    >
                                        <X class="h-3 w-3" />
                                    </button>
                                </Badge>
                            </div>
                            <div class="flex gap-2">
                                <Input
                                    v-model="newTag"
                                    placeholder="Add a tag..."
                                    @keypress="handleKeyPress"
                                    class="flex-1"
                                />
                                <Button
                                    type="button"
                                    variant="outline"
                                    size="sm"
                                    @click="addTag"
                                    :disabled="!newTag.trim()"
                                >
                                    <Tag class="h-4 w-4" />
                                </Button>
                            </div>
                        </div>

                        <!-- Favorite Toggle -->
                        <div class="flex items-center space-x-2">
                            <Switch
                                id="is_favorite"
                                v-model:checked="form.is_favorite"
                            />
                            <Label for="is_favorite">Mark as favorite</Label>
                        </div>

                        <!-- Actions -->
                        <div class="flex gap-3 pt-4">
                            <Button
                                type="button"
                                variant="outline"
                                @click="$inertia.visit(route('contacts.index'))"
                            >
                                Cancel
                            </Button>
                            <Button
                                type="submit"
                                :disabled="form.processing"
                                class="min-w-24"
                            >
                                <UserPlus v-if="!form.processing" class="mr-2 h-4 w-4" />
                                {{ form.processing ? 'Creating...' : 'Create Contact' }}
                            </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </div>
    </AppLayout>
</template>
