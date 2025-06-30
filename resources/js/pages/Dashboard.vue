<script setup lang="ts">
import AppLayout from '@/layouts/AppLayout.vue';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Badge } from '@/components/ui/badge';
import { type BreadcrumbItem } from '@/types';
import { Head, Link, usePage } from '@inertiajs/vue3';
import { 
    Phone, 
    MessageSquare, 
    Settings, 
    CheckCircle2, 
    AlertCircle, 
    TrendingUp,
    Clock,
    Users,
    Activity,
    ArrowRight
} from 'lucide-vue-next';
import { computed } from 'vue';

interface DashboardStats {
    total_calls: number;
    total_sms: number;
    total_errors: number;
    recent_activity: Array<{
        id: number;
        type: string;
        from_number: string;
        status: string;
        created_at: string;
    }>;
    twilio_configured: boolean;
}

interface Props {
    stats: DashboardStats;
}

const props = defineProps<Props>();
const page = usePage();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Dashboard',
        href: '/dashboard',
    },
];

const getStatusColor = (status: string) => {
    switch (status) {
        case 'processed':
        case 'sent':
            return 'default';
        case 'received':
            return 'secondary';
        case 'error':
            return 'destructive';
        default:
            return 'outline';
    }
};

const formatDate = (dateString: string) => {
    const date = new Date(dateString);
    const now = new Date();
    const diffInMinutes = Math.floor((now.getTime() - date.getTime()) / (1000 * 60));
    
    if (diffInMinutes < 1) return 'Just now';
    if (diffInMinutes < 60) return `${diffInMinutes}m ago`;
    if (diffInMinutes < 1440) return `${Math.floor(diffInMinutes / 60)}h ago`;
    return date.toLocaleDateString();
};

const formatPhoneNumber = (phoneNumber: string) => {
    if (!phoneNumber) return '';
    const cleaned = phoneNumber.replace(/\D/g, '');
    if (cleaned.length === 11 && cleaned.startsWith('1')) {
        return `+1 (${cleaned.slice(1, 4)}) ${cleaned.slice(4, 7)}-${cleaned.slice(7)}`;
    }
    return phoneNumber;
};
</script>

<template>
    <Head title="Dashboard" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 rounded-xl p-4">
            <!-- Header -->
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold tracking-tight">Dashboard</h1>
                    <p class="text-muted-foreground">
                        Monitor your Twilio integration and webhook activity
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    <Button variant="outline" as-child>
                        <Link :href="route('twilio.logs')">
                            View All Logs
                        </Link>
                    </Button>
                    <Button as-child>
                        <Link :href="route('twilio.settings')">
                            <Settings class="mr-2 h-4 w-4" />
                            Settings
                        </Link>
                    </Button>
                </div>
            </div>

            <!-- Configuration Status -->
            <Card v-if="!props.stats.twilio_configured" class="border-orange-200 bg-orange-50">
                <CardContent class="flex items-center gap-4 p-6">
                    <div class="rounded-full bg-orange-100 p-3">
                        <AlertCircle class="h-6 w-6 text-orange-600" />
                    </div>
                    <div class="flex-1">
                        <h3 class="font-semibold text-orange-900">Setup Required</h3>
                        <p class="text-sm text-orange-700">
                            Configure your Twilio settings to start receiving calls and SMS messages.
                        </p>
                    </div>
                    <Button as-child>
                        <Link :href="route('twilio.settings')">
                            Configure Now
                            <ArrowRight class="ml-2 h-4 w-4" />
                        </Link>
                    </Button>
                </CardContent>
            </Card>

            <Card v-else class="border-green-200 bg-green-50">
                <CardContent class="flex items-center gap-4 p-6">
                    <div class="rounded-full bg-green-100 p-3">
                        <CheckCircle2 class="h-6 w-6 text-green-600" />
                    </div>
                    <div class="flex-1">
                        <h3 class="font-semibold text-green-900">Twilio Integration Active</h3>
                        <p class="text-sm text-green-700">
                            Your Twilio integration is configured and ready to handle calls and SMS.
                        </p>
                    </div>
                </CardContent>
            </Card>

            <!-- Stats Cards -->
            <div class="grid gap-6 md:grid-cols-4">
                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Total Calls</CardTitle>
                        <Phone class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ props.stats.total_calls }}</div>
                        <p class="text-xs text-muted-foreground">
                            Voice calls received
                        </p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">SMS Messages</CardTitle>
                        <MessageSquare class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ props.stats.total_sms }}</div>
                        <p class="text-xs text-muted-foreground">
                            Messages processed
                        </p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Success Rate</CardTitle>
                        <TrendingUp class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">
                            {{ props.stats.total_calls + props.stats.total_sms > 0 
                                ? Math.round(((props.stats.total_calls + props.stats.total_sms - props.stats.total_errors) / (props.stats.total_calls + props.stats.total_sms)) * 100)
                                : 100 }}%
                        </div>
                        <p class="text-xs text-muted-foreground">
                            Processing success rate
                        </p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader class="flex flex-row items-center justify-between space-y-0 pb-2">
                        <CardTitle class="text-sm font-medium">Errors</CardTitle>
                        <AlertCircle class="h-4 w-4 text-muted-foreground" />
                    </CardHeader>
                    <CardContent>
                        <div class="text-2xl font-bold">{{ props.stats.total_errors }}</div>
                        <p class="text-xs text-muted-foreground">
                            Failed processing attempts
                        </p>
                    </CardContent>
                </Card>
            </div>

            <!-- Recent Activity -->
            <div class="grid gap-6 md:grid-cols-2">
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <Activity class="h-5 w-5" />
                            Recent Activity
                        </CardTitle>
                        <CardDescription>
                            Latest webhook calls and messages
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="space-y-4">
                            <div
                                v-for="activity in props.stats.recent_activity.slice(0, 5)"
                                :key="activity.id"
                                class="flex items-center justify-between"
                            >
                                <div class="flex items-center gap-3">
                                    <div class="rounded-full bg-muted p-2">
                                        <Phone v-if="activity.type === 'voice'" class="h-4 w-4" />
                                        <MessageSquare v-else class="h-4 w-4" />
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium">
                                            {{ activity.type === 'voice' ? 'Call' : 'SMS' }} from
                                            {{ formatPhoneNumber(activity.from_number) }}
                                        </p>
                                        <p class="text-xs text-muted-foreground">
                                            {{ formatDate(activity.created_at) }}
                                        </p>
                                    </div>
                                </div>
                                <Badge :variant="getStatusColor(activity.status)">
                                    {{ activity.status }}
                                </Badge>
                            </div>
                            <div v-if="props.stats.recent_activity.length === 0" class="text-center py-8">
                                <Clock class="mx-auto h-12 w-12 text-muted-foreground/30" />
                                <p class="mt-2 text-sm text-muted-foreground">
                                    No recent activity
                                </p>
                                <p class="text-xs text-muted-foreground">
                                    Activity will appear here when you receive calls or SMS
                                </p>
                            </div>
                        </div>
                        <div v-if="props.stats.recent_activity.length > 5" class="mt-4 text-center">
                            <Button variant="outline" size="sm" as-child>
                                <Link :href="route('twilio.logs')">
                                    View All Activity
                                </Link>
                            </Button>
                        </div>
                    </CardContent>
                </Card>

                <!-- Quick Actions -->
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center gap-2">
                            <Settings class="h-5 w-5" />
                            Quick Actions
                        </CardTitle>
                        <CardDescription>
                            Common tasks and configuration
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-3">
                        <Button variant="outline" class="w-full justify-start" as-child>
                            <Link :href="route('twilio.settings')">
                                <Settings class="mr-2 h-4 w-4" />
                                Configure Twilio Settings
                            </Link>
                        </Button>
                        <Button variant="outline" class="w-full justify-start" as-child>
                            <Link :href="route('twilio.logs')">
                                <Activity class="mr-2 h-4 w-4" />
                                View Webhook Logs
                            </Link>
                        </Button>
                        <Button variant="outline" class="w-full justify-start" as-child>
                            <Link :href="route('profile.edit')">
                                <Users class="mr-2 h-4 w-4" />
                                Account Settings
                            </Link>
                        </Button>
                    </CardContent>
                </Card>
            </div>

            <!-- Info Cards -->
            <div class="grid gap-6 md:grid-cols-2">
                <Card>
                    <CardHeader>
                        <CardTitle>How It Works</CardTitle>
                        <CardDescription>
                            Understanding your Twilio integration
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="flex gap-3">
                            <div class="mt-0.5">
                                <div class="flex h-6 w-6 items-center justify-center rounded-full bg-blue-100 text-sm font-medium text-blue-600">
                                    1
                                </div>
                            </div>
                            <div>
                                <p class="text-sm font-medium">Incoming Calls</p>
                                <p class="text-xs text-muted-foreground">
                                    Calls to your Twilio number are forwarded to your configured phone or SIP endpoint
                                </p>
                            </div>
                        </div>
                        <div class="flex gap-3">
                            <div class="mt-0.5">
                                <div class="flex h-6 w-6 items-center justify-center rounded-full bg-blue-100 text-sm font-medium text-blue-600">
                                    2
                                </div>
                            </div>
                            <div>
                                <p class="text-sm font-medium">SMS Messages</p>
                                <p class="text-xs text-muted-foreground">
                                    Text messages are optionally forwarded to your phone number
                                </p>
                            </div>
                        </div>
                        <div class="flex gap-3">
                            <div class="mt-0.5">
                                <div class="flex h-6 w-6 items-center justify-center rounded-full bg-blue-100 text-sm font-medium text-blue-600">
                                    3
                                </div>
                            </div>
                            <div>
                                <p class="text-sm font-medium">Activity Logging</p>
                                <p class="text-xs text-muted-foreground">
                                    All webhook activity is logged for monitoring and debugging
                                </p>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>Next Steps</CardTitle>
                        <CardDescription>
                            Get the most out of your setup
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-4">
                        <div class="space-y-2">
                            <p class="text-sm font-medium">1. Configure Webhook URLs</p>
                            <p class="text-xs text-muted-foreground">
                                Add the webhook URLs from settings to your Twilio Console
                            </p>
                        </div>
                        <div class="space-y-2">
                            <p class="text-sm font-medium">2. Test Your Setup</p>
                            <p class="text-xs text-muted-foreground">
                                Make a test call or send an SMS to verify everything works
                            </p>
                        </div>
                        <div class="space-y-2">
                            <p class="text-sm font-medium">3. Monitor Activity</p>
                            <p class="text-xs text-muted-foreground">
                                Check the logs regularly to ensure proper operation
                            </p>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>
    </AppLayout>
</template>
