<script setup lang="ts">
import { Link, router } from '@inertiajs/vue3';
import { ref } from 'vue';
import { Card, CardContent, CardHeader } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { Avatar, AvatarFallback } from '@/components/ui/avatar';
import { 
    Phone, 
    MessageSquare, 
    Mail, 
    Star,
    MoreVertical,
    Edit,
    Trash2,
    User
} from 'lucide-vue-next';
import { DropdownMenu, DropdownMenuContent, DropdownMenuItem, DropdownMenuSeparator, DropdownMenuTrigger } from '@/components/ui/dropdown-menu';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Textarea } from '@/components/ui/textarea';
import { Label } from '@/components/ui/label';
import { toast } from '@/components/ui/toast';
import axios from 'axios';

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

interface Props {
    contact: Contact;
}

const props = defineProps<Props>();
const emit = defineEmits<{
    toggleFavorite: [contactId: number];
    delete: [contactId: number];
}>();

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

// Handle call action
const handleCall = async () => {
    isLoading.value = true;
    try {
        const response = await axios.post(route('contacts.call', props.contact.id), {
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            },
        });

        const data = response.data;

        if (response.status === 200) {
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
        const response = await axios.post(route('contacts.sms', props.contact.id), {
                message: smsMessage.value,
            },{
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '',
            },
        });
        
        const data = await response.data;

        if (response.status === 200) {
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
</script>

<template>
    <Card class="group hover:shadow-md transition-shadow duration-200">
        <CardHeader class="pb-3">
            <div class="flex items-start justify-between">
                <div class="flex items-center space-x-3">
                    <Avatar class="h-10 w-10">
                        <AvatarFallback class="bg-primary/10 text-primary font-semibold">
                            {{ getInitials(contact.name) }}
                        </AvatarFallback>
                    </Avatar>
                    <div>
                        <h3 class="font-semibold text-lg">{{ contact.name }}</h3>
                        <p class="text-sm text-muted-foreground font-mono">
                            {{ contact.formatted_phone }}
                        </p>
                    </div>
                </div>
                <div class="flex items-center space-x-2">
                    <Button
                        variant="ghost"
                        size="sm"
                        @click="emit('toggleFavorite', contact.id)"
                        class="opacity-0 group-hover:opacity-100 transition-opacity"
                        :class="{ 'opacity-100 text-yellow-500': contact.is_favorite }"
                    >
                        <Star class="h-4 w-4" :class="{ 'fill-current': contact.is_favorite }" />
                    </Button>
                    <DropdownMenu>
                        <DropdownMenuTrigger as-child>
                            <Button variant="ghost" size="sm" class="opacity-0 group-hover:opacity-100 transition-opacity">
                                <MoreVertical class="h-4 w-4" />
                            </Button>
                        </DropdownMenuTrigger>
                        <DropdownMenuContent align="end">
                            <DropdownMenuItem as-child>
                                <Link :href="route('contacts.show', contact.id)">
                                    <User class="mr-2 h-4 w-4" />
                                    View Details
                                </Link>
                            </DropdownMenuItem>
                            <DropdownMenuItem as-child>
                                <Link :href="route('contacts.edit', contact.id)">
                                    <Edit class="mr-2 h-4 w-4" />
                                    Edit Contact
                                </Link>
                            </DropdownMenuItem>
                            <DropdownMenuSeparator />
                            <DropdownMenuItem 
                                class="text-destructive"
                                @click="emit('delete', contact.id)"
                            >
                                <Trash2 class="mr-2 h-4 w-4" />
                                Delete Contact
                            </DropdownMenuItem>
                        </DropdownMenuContent>
                    </DropdownMenu>
                </div>
            </div>
        </CardHeader>
        
        <CardContent class="space-y-4">
            <!-- Contact Info -->
            <div class="space-y-2">
                <div v-if="contact.email" class="flex items-center text-sm text-muted-foreground">
                    <Mail class="mr-2 h-4 w-4" />
                    {{ contact.email }}
                </div>
                <div v-if="contact.notes" class="text-sm text-muted-foreground">
                    <p class="line-clamp-2">{{ contact.notes }}</p>
                </div>
            </div>

            <!-- Tags -->
            <div v-if="contact.tags && contact.tags.length > 0" class="flex flex-wrap gap-1">
                <Badge 
                    v-for="tag in contact.tags.slice(0, 3)" 
                    :key="tag"
                    variant="secondary"
                    class="text-xs"
                >
                    {{ tag }}
                </Badge>
                <Badge 
                    v-if="contact.tags.length > 3"
                    variant="outline"
                    class="text-xs"
                >
                    +{{ contact.tags.length - 3 }}
                </Badge>
            </div>

            <!-- Action Buttons -->
            <div class="flex space-x-2">
                <Button 
                    size="sm" 
                    @click="handleCall"
                    :disabled="isLoading"
                    class="flex-1"
                >
                    <Phone class="mr-2 h-4 w-4" />
                    Call
                </Button>
                <Button 
                    size="sm" 
                    variant="outline" 
                    @click="handleSms"
                    :disabled="isLoading"
                    class="flex-1"
                >
                    <MessageSquare class="mr-2 h-4 w-4" />
                    Text
                </Button>
                <Button 
                    size="sm" 
                    variant="outline" 
                    @click="handleEmail"
                    :disabled="!contact.email"
                    class="px-3"
                >
                    <Mail class="h-4 w-4" />
                </Button>
            </div>
        </CardContent>

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
    </Card>
</template>

<style scoped>
.line-clamp-2 {
    display: -webkit-box;
    -webkit-line-clamp: 2;
    line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
}
</style>
