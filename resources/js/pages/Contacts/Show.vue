<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Avatar, AvatarFallback } from '@/components/ui/avatar';
import { Separator } from '@/components/ui/separator';
import { 
    User, 
    Phone, 
    MessageSquare, 
    Mail, 
    Calendar,
    Edit,
    Trash2,
    Star,
    Tag as TagIcon
} from 'lucide-vue-next';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Textarea } from '@/components/ui/textarea';
import { Label } from '@/components/ui/label';
import { toast } from '@/components/ui/toast';
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
    user: {
        id: number;
        name: string;
        email: string;
    };
}

interface Props {
    contact: Contact;
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Contacts',
        href: '/contacts',
    },
    {
        title: props.contact.name,
        href: `/contacts/${props.contact.id}`,
    },
];

const showSmsDialog = ref(false);
const smsMessage = ref('');
const isLoading = ref(false);

// Get initials for avatar
const getInitials = (name: string): string => {
    return name
        .split(' ')
        .map(word => word.charAt(0).toUpperCase())
        .slice(0, 2)
        .join('');
};

// Format date
const formatDate = (dateString: string): string => {
    return new Date(dateString).toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });
};

// Handle call action
const handleCall = async () => {
    isLoading.value = true;
    try {
        const response = await fetch(route('contacts.call', props.contact.id), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            },
        });
        
        const data = await response.json();
        
        if (response.ok) {
            toast({
                title: "Call Instructions",
                description: data.message,
            });
        } else {
            toast({
                title: "Error",
                description: data.error || "Failed to initiate call",
                variant: "destructive",
            });
        }
    } catch (error) {
        toast({
            title: "Error",
            description: "Failed to connect to server",
            variant: "destructive",
        });
    } finally {
        isLoading.value = false;
    }
};

// Handle SMS action
const handleSms = () => {
    smsMessage.value = '';
    showSmsDialog.value = true;
};

const sendSms = async () => {
    if (!smsMessage.value.trim()) return;
    
    isLoading.value = true;
    try {
        const response = await fetch(route('contacts.sms', props.contact.id), {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            },
            body: JSON.stringify({
                message: smsMessage.value,
            }),
        });
        
        const data = await response.json();
        
        if (response.ok) {
            toast({
                title: "SMS Status",
                description: data.message,
            });
            showSmsDialog.value = false;
        } else {
            toast({
                title: "Error",
                description: data.error || "Failed to send SMS",
                variant: "destructive",
            });
        }
    } catch (error) {
        toast({
            title: "Error",
            description: "Failed to connect to server",
            variant: "destructive",
        });
    } finally {
        isLoading.value = false;
    }
};

// Handle email action
const handleEmail = () => {
    if (props.contact.email) {
        window.location.href = `mailto:${props.contact.email}`;
    }
};

// Handle toggle favorite
const handleToggleFavorite = async () => {
    try {
        await router.post(route('contacts.toggle-favorite', props.contact.id), {}, {
            preserveState: true,
            preserveScroll: true,
        });
    } catch (error) {
        console.error('Failed to toggle favorite:', error);
    }
};

// Handle delete
const handleDelete = () => {
    if (confirm('Are you sure you want to delete this contact?')) {
        router.delete(route('contacts.destroy', props.contact.id));
    }
};
</script>

<template>
    <AppLayout :breadcrumbs="breadcrumbs">
        <Head :title="contact.name" />

        <div class="max-w-4xl mx-auto space-y-6">
            <!-- Header -->
            <div class="flex items-start justify-between">
                <div class="flex items-center space-x-4">
                    <Avatar class="h-16 w-16">
                        <AvatarFallback class="bg-primary/10 text-primary font-bold text-xl">
                            {{ getInitials(contact.name) }}
                        </AvatarFallback>
                    </Avatar>
                    <div>
                        <div class="flex items-center gap-2">
                            <h1 class="text-3xl font-bold tracking-tight">{{ contact.name }}</h1>
                            <Button
                                variant="ghost"
                                size="sm"
                                @click="handleToggleFavorite"
                                :class="{ 'text-yellow-500': contact.is_favorite }"
                            >
                                <Star class="h-5 w-5" :class="{ 'fill-current': contact.is_favorite }" />
                            </Button>
                        </div>
                        <p class="text-lg text-muted-foreground font-mono">
                            {{ contact.formatted_phone }}
                        </p>
                    </div>
                </div>
                <div class="flex gap-2">
                    <Button variant="outline" size="sm" as-child>
                        <Link :href="route('contacts.edit', contact.id)">
                            <Edit class="mr-2 h-4 w-4" />
                            Edit
                        </Link>
                    </Button>
                    <Button variant="outline" size="sm" @click="handleDelete" class="text-destructive hover:text-destructive">
                        <Trash2 class="mr-2 h-4 w-4" />
                        Delete
                    </Button>
                </div>
            </div>

            <div class="grid gap-6 lg:grid-cols-3">
                <!-- Contact Information -->
                <div class="lg:col-span-2 space-y-6">
                    <!-- Quick Actions -->
                    <Card>
                        <CardHeader>
                            <CardTitle>Quick Actions</CardTitle>
                            <CardDescription>
                                Contact {{ contact.name }} directly
                            </CardDescription>
                        </CardHeader>
                        <CardContent>
                            <div class="flex flex-wrap gap-3">
                                <Button @click="handleCall" :disabled="isLoading" class="flex-1 min-w-32">
                                    <Phone class="mr-2 h-4 w-4" />
                                    Call
                                </Button>
                                <Button variant="outline" @click="handleSms" :disabled="isLoading" class="flex-1 min-w-32">
                                    <MessageSquare class="mr-2 h-4 w-4" />
                                    Send SMS
                                </Button>
                                <Button 
                                    variant="outline" 
                                    @click="handleEmail" 
                                    :disabled="!contact.email"
                                    class="flex-1 min-w-32"
                                >
                                    <Mail class="mr-2 h-4 w-4" />
                                    Email
                                </Button>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Contact Details -->
                    <Card>
                        <CardHeader>
                            <CardTitle>Contact Details</CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div class="grid gap-4 md:grid-cols-2">
                                <div class="space-y-2">
                                    <Label class="text-sm font-medium text-muted-foreground">Phone Number</Label>
                                    <div class="flex items-center space-x-2">
                                        <Phone class="h-4 w-4 text-muted-foreground" />
                                        <span class="font-mono">{{ contact.formatted_phone }}</span>
                                    </div>
                                </div>

                                <div v-if="contact.email" class="space-y-2">
                                    <Label class="text-sm font-medium text-muted-foreground">Email Address</Label>
                                    <div class="flex items-center space-x-2">
                                        <Mail class="h-4 w-4 text-muted-foreground" />
                                        <a 
                                            :href="`mailto:${contact.email}`"
                                            class="text-primary hover:underline"
                                        >
                                            {{ contact.email }}
                                        </a>
                                    </div>
                                </div>
                            </div>

                            <div v-if="contact.notes" class="space-y-2">
                                <Label class="text-sm font-medium text-muted-foreground">Notes</Label>
                                <div class="p-3 bg-muted rounded-md">
                                    <p class="text-sm whitespace-pre-wrap">{{ contact.notes }}</p>
                                </div>
                            </div>

                            <div v-if="contact.tags && contact.tags.length > 0" class="space-y-2">
                                <Label class="text-sm font-medium text-muted-foreground">Tags</Label>
                                <div class="flex flex-wrap gap-2">
                                    <Badge
                                        v-for="tag in contact.tags"
                                        :key="tag"
                                        variant="secondary"
                                        class="flex items-center gap-1"
                                    >
                                        <TagIcon class="h-3 w-3" />
                                        {{ tag }}
                                    </Badge>
                                </div>
                            </div>
                        </CardContent>
                    </Card>
                </div>

                <!-- Sidebar -->
                <div class="space-y-6">
                    <!-- Contact Info -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="flex items-center gap-2">
                                <User class="h-5 w-5" />
                                Contact Info
                            </CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div class="space-y-2">
                                <Label class="text-sm font-medium text-muted-foreground">Status</Label>
                                <Badge :variant="contact.is_favorite ? 'default' : 'secondary'">
                                    {{ contact.is_favorite ? 'Favorite' : 'Regular' }}
                                </Badge>
                            </div>

                            <Separator />

                            <div class="space-y-2">
                                <Label class="text-sm font-medium text-muted-foreground">Created</Label>
                                <div class="flex items-center space-x-2 text-sm">
                                    <Calendar class="h-4 w-4 text-muted-foreground" />
                                    <span>{{ formatDate(contact.created_at) }}</span>
                                </div>
                            </div>

                            <div v-if="contact.updated_at !== contact.created_at" class="space-y-2">
                                <Label class="text-sm font-medium text-muted-foreground">Last Updated</Label>
                                <div class="flex items-center space-x-2 text-sm">
                                    <Calendar class="h-4 w-4 text-muted-foreground" />
                                    <span>{{ formatDate(contact.updated_at) }}</span>
                                </div>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- Actions -->
                    <Card>
                        <CardHeader>
                            <CardTitle>Actions</CardTitle>
                        </CardHeader>
                        <CardContent class="space-y-3">
                            <Button variant="outline" class="w-full" as-child>
                                <Link :href="route('contacts.edit', contact.id)">
                                    <Edit class="mr-2 h-4 w-4" />
                                    Edit Contact
                                </Link>
                            </Button>
                            <Button variant="outline" class="w-full" @click="handleToggleFavorite">
                                <Star class="mr-2 h-4 w-4" />
                                {{ contact.is_favorite ? 'Remove from Favorites' : 'Add to Favorites' }}
                            </Button>
                            <Button 
                                variant="outline" 
                                class="w-full text-destructive hover:text-destructive" 
                                @click="handleDelete"
                            >
                                <Trash2 class="mr-2 h-4 w-4" />
                                Delete Contact
                            </Button>
                        </CardContent>
                    </Card>
                </div>
            </div>
        </div>

        <!-- SMS Dialog -->
        <Dialog v-model:open="showSmsDialog">
            <DialogContent class="sm:max-w-md">
                <DialogHeader>
                    <DialogTitle>Send SMS to {{ contact.name }}</DialogTitle>
                    <DialogDescription>
                        Send a text message to {{ contact.formatted_phone }}
                    </DialogDescription>
                </DialogHeader>
                
                <div class="space-y-4">
                    <div class="space-y-2">
                        <Label for="message">Message</Label>
                        <Textarea
                            id="message"
                            v-model="smsMessage"
                            placeholder="Type your message here..."
                            rows="4"
                            maxlength="1600"
                            class="resize-none"
                        />
                        <p class="text-xs text-muted-foreground text-right">
                            {{ smsMessage.length }}/1600 characters
                        </p>
                    </div>
                </div>

                <DialogFooter>
                    <Button variant="outline" @click="showSmsDialog = false">
                        Cancel
                    </Button>
                    <Button 
                        @click="sendSms" 
                        :disabled="!smsMessage.trim() || isLoading"
                    >
                        <MessageSquare class="mr-2 h-4 w-4" />
                        Send SMS
                    </Button>
                </DialogFooter>
            </DialogContent>
        </Dialog>
    </AppLayout>
</template>
