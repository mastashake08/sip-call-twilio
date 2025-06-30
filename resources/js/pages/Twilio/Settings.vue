<script setup lang="ts">
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { computed } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Textarea } from '@/components/ui/textarea';
import { Switch } from '@/components/ui/switch';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Separator } from '@/components/ui/separator';
import { Badge } from '@/components/ui/badge';
import { Alert, AlertDescription } from '@/components/ui/alert';
import InputError from '@/components/InputError.vue';
import { Phone, MessageSquare, Settings, AlertCircle, CheckCircle2 } from 'lucide-vue-next';
import type { BreadcrumbItem } from '@/types';

interface TwilioSettings {
    id?: number;
    twilio_phone_number?: string;
    forward_to_phone?: string;
    sip_endpoint?: string;
    sip_username?: string;
    sip_password?: string;
    call_action: 'dial_phone' | 'dial_sip';
    sms_forwarding_enabled: boolean;
    custom_greeting?: string;
}

interface Props {
    settings: TwilioSettings;
}

const props = defineProps<Props>();
const $page = usePage();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Twilio Settings',
        href: '/twilio/settings',
    },
];

const smsUrl = route('twilio.webhook.sms');
const voiceUrl = route('twilio.webhook.voice');

const form = useForm({
    twilio_phone_number: props.settings.twilio_phone_number || '',
    forward_to_phone: props.settings.forward_to_phone || '',
    sip_endpoint: props.settings.sip_endpoint || '',
    sip_username: props.settings.sip_username || '',
    sip_password: props.settings.sip_password || '',
    call_action: props.settings.call_action || 'dial_phone',
    sms_forwarding_enabled: props.settings.sms_forwarding_enabled ?? true,
    custom_greeting: props.settings.custom_greeting || '',
});

const submit = () => {
    console.log('Form data:', form.data());
    console.log('Custom greeting:', form.custom_greeting);
    form.post(route('twilio.settings.update'), {
        preserveScroll: true,
    });
};

const callActionOptions = [
    { value: 'dial_phone', label: 'Forward to Phone Number' },
    { value: 'dial_sip', label: 'Forward to SIP Endpoint' },
];

const isConfigured = computed(() => {
    return Boolean(props.settings.twilio_phone_number);
});
</script>

<template>
    <Head title="Twilio Settings" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="container mx-auto px-4 py-8 max-w-4xl">
            <div class="space-y-6">
                <!-- Header -->
                <div class="flex items-center gap-3">
                    <Phone class="h-8 w-8 text-primary" />
                    <div>
                        <h1 class="text-3xl font-bold">Twilio Settings</h1>
                        <p class="text-muted-foreground">
                            Configure your Twilio phone number and call forwarding settings
                        </p>
                    </div>
                </div>

                <!-- Status Alert -->
                <Alert :class="isConfigured ? 'border-green-200 bg-green-50' : 'border-yellow-200 bg-yellow-50'">
                    <CheckCircle2 v-if="isConfigured" class="h-4 w-4 text-green-600" />
                    <AlertCircle v-else class="h-4 w-4 text-yellow-600" />
                    <AlertDescription :class="isConfigured ? 'text-green-800' : 'text-yellow-800'">
                        <strong v-if="isConfigured">Configuration Complete:</strong>
                        <strong v-else>Setup Required:</strong>
                        {{ isConfigured 
                            ? 'Your Twilio integration is configured and ready to receive calls and SMS.' 
                            : 'Please configure your Twilio phone number and forwarding settings below.' 
                        }}
                    </AlertDescription>
                </Alert>

                <form @submit.prevent="submit" class="space-y-8">
                    <!-- Phone Configuration -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="flex items-center gap-2">
                                <Phone class="h-5 w-5" />
                                Phone Number Setup
                            </CardTitle>
                            <CardDescription>
                                Configure your Twilio phone number and call forwarding behavior
                            </CardDescription>
                        </CardHeader>
                        <CardContent class="space-y-6">
                            <div class="grid gap-4 md:grid-cols-2">
                                <div class="space-y-2">
                                    <Label for="twilio_phone_number">Twilio Phone Number</Label>
                                    <Input
                                        id="twilio_phone_number"
                                        v-model="form.twilio_phone_number"
                                        placeholder="+1234567890"
                                        class="font-mono"
                                    />
                                    <InputError :message="form.errors.twilio_phone_number" />
                                    <p class="text-xs text-muted-foreground">
                                        Your Twilio phone number (including country code)
                                    </p>
                                </div>

                                <div class="space-y-2">
                                    <Label for="call_action">Call Action</Label>
                                    <Select v-model="form.call_action">
                                        <SelectTrigger>
                                            <SelectValue placeholder="Select call action" />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem
                                                v-for="option in callActionOptions"
                                                :key="option.value"
                                                :value="option.value"
                                            >
                                                {{ option.label }}
                                            </SelectItem>
                                        </SelectContent>
                                    </Select>
                                    <InputError :message="form.errors.call_action" />
                                </div>
                            </div>

                            <div class="grid gap-4 md:grid-cols-2">
                                <div class="space-y-2" v-show="form.call_action === 'dial_phone'">
                                    <Label for="forward_to_phone">Forward to Phone</Label>
                                    <Input
                                        id="forward_to_phone"
                                        v-model="form.forward_to_phone"
                                        placeholder="+1234567890"
                                        class="font-mono"
                                    />
                                    <InputError :message="form.errors.forward_to_phone" />
                                    <p class="text-xs text-muted-foreground">
                                        Phone number to forward calls to
                                    </p>
                                </div>

                                <div class="space-y-2" v-show="form.call_action === 'dial_sip'">
                                    <Label for="sip_endpoint">SIP Endpoint</Label>
                                    <Input
                                        id="sip_endpoint"
                                        v-model="form.sip_endpoint"
                                        placeholder="sip:user@domain.com"
                                        class="font-mono"
                                    />
                                    <InputError :message="form.errors.sip_endpoint" />
                                    <p class="text-xs text-muted-foreground">
                                        SIP endpoint URI to dial
                                    </p>
                                </div>
                            </div>

                            <!-- SIP Authentication Fields (only show when SIP is selected) -->
                            <div v-show="form.call_action === 'dial_sip'" class="space-y-4">
                                <div class="p-4 bg-blue-50 border border-blue-200 rounded-md">
                                    <h4 class="text-sm font-medium text-blue-900 mb-2">SIP Authentication</h4>
                                    <p class="text-xs text-blue-700">
                                        If your SIP endpoint requires authentication, provide the username and password below. 
                                        Leave empty if your SIP endpoint doesn't require authentication.
                                    </p>
                                </div>
                                
                                <div class="grid gap-4 md:grid-cols-2">
                                    <div class="space-y-2">
                                        <Label for="sip_username">SIP Username (Optional)</Label>
                                        <Input
                                            id="sip_username"
                                            v-model="form.sip_username"
                                            placeholder="username"
                                            class="font-mono"
                                        />
                                        <InputError :message="form.errors.sip_username" />
                                        <p class="text-xs text-muted-foreground">
                                            Username for SIP authentication
                                        </p>
                                    </div>

                                    <div class="space-y-2">
                                        <Label for="sip_password">SIP Password (Optional)</Label>
                                        <Input
                                            id="sip_password"
                                            v-model="form.sip_password"
                                            type="password"
                                            placeholder="••••••••"
                                            class="font-mono"
                                        />
                                        <InputError :message="form.errors.sip_password" />
                                        <p class="text-xs text-muted-foreground">
                                            Password for SIP authentication
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="space-y-2">
                                <Label for="custom_greeting">Custom Greeting (Optional)</Label>
                                <Textarea
                                    id="custom_greeting"
                                    v-model="form.custom_greeting"
                                    placeholder="Hello! Please hold while we connect your call..."
                                    rows="3"
                                />
                                <InputError :message="form.errors.custom_greeting" />
                                <p class="text-xs text-muted-foreground">
                                    Custom message to play before connecting the call
                                </p>
                            </div>
                        </CardContent>
                    </Card>

                    <!-- SMS Configuration -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="flex items-center gap-2">
                                <MessageSquare class="h-5 w-5" />
                                SMS Forwarding
                            </CardTitle>
                            <CardDescription>
                                Configure how SMS messages are handled
                            </CardDescription>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div class="flex items-center justify-between">
                                <div class="space-y-0.5">
                                    <Label class="text-base">Enable SMS Forwarding</Label>
                                    <p class="text-sm text-muted-foreground">
                                        Forward received SMS messages to your phone number
                                    </p>
                                </div>
                                <Switch
                                    v-model="form.sms_forwarding_enabled"
                                />
                            </div>
                            <InputError :message="form.errors.sms_forwarding_enabled" />
                        </CardContent>
                    </Card>

                    <!-- Webhook Information -->
                    <Card>
                        <CardHeader>
                            <CardTitle class="flex items-center gap-2">
                                <Settings class="h-5 w-5" />
                                Webhook Configuration
                            </CardTitle>
                            <CardDescription>
                                Configure these webhook URLs in your Twilio console
                            </CardDescription>
                        </CardHeader>
                        <CardContent class="space-y-4">
                            <div class="space-y-3">
                                <div>
                                    <Label class="text-sm font-medium">Voice Webhook URL</Label>
                                    <div class="flex items-center gap-2 mt-1">
                                        <Input
                                            id="voice-webhook"
                                            type="url"
                                            name="voice-webhook"
                                            v-model="voiceUrl"
                                            readonly
                                            class="font-mono text-sm !text-gray-600 dark:!text-gray-300 bg-gray-50 dark:bg-gray-800"
                                        />
                                        <Badge variant="secondary">POST</Badge>
                                    </div>
                                </div>
                                
                                <div>
                                    <Label class="text-sm font-medium">SMS Webhook URL</Label>
                                    <div class="flex items-center gap-2 mt-1">
                                        <Input
                                            id="sms-webhook" type="url" name="sms-webhook"
                                            v-model="smsUrl"
                                            readonly
                                            class="font-mono text-sm !text-gray-600 dark:!text-gray-300 bg-gray-50 dark:bg-gray-800"
                                        />
                                        <Badge variant="secondary">POST</Badge>
                                    </div>
                                </div>
                            </div>
                            
                            <Alert>
                                <AlertCircle class="h-4 w-4" />
                                <AlertDescription>
                                    Copy these URLs and configure them in your Twilio Console under 
                                    Phone Numbers → Manage → Active numbers → [Your Number] → Webhook configuration.
                                </AlertDescription>
                            </Alert>
                        </CardContent>
                    </Card>

                    <!-- Save Button -->
                    <div class="flex justify-end">
                        <Button type="submit" :disabled="form.processing" class="min-w-32">
                            <span v-if="form.processing">Saving...</span>
                            <span v-else>Save Settings</span>
                        </Button>
                    </div>
                </form>
            </div>
        </div>
    </AppLayout>
</template>
