<script setup lang="ts">
import { Head, Link, router } from '@inertiajs/vue3';
import { ref, computed, onMounted } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Select, SelectContent, SelectItem, SelectTrigger, SelectValue } from '@/components/ui/select';
import { Badge } from '@/components/ui/badge';
import { Table, TableBody, TableCell, TableHead, TableHeader, TableRow } from '@/components/ui/table';
import { Dialog, DialogContent, DialogDescription, DialogHeader, DialogTitle, DialogTrigger } from '@/components/ui/dialog';
import { ScrollArea } from '@/components/ui/scroll-area';
import {
    Phone,
    MessageSquare,
    Clock,
    CheckCircle,
    XCircle,
    AlertCircle,
    Search,
    Filter,
    Eye,
    RefreshCw,
    Settings,
} from 'lucide-vue-next';
import { type BreadcrumbItem } from '@/types';

interface WebhookLog {
    id: number;
    type: 'voice' | 'sms' | 'sms_forward';
    from_number: string;
    to_number: string;
    content?: string;
    call_sid?: string;
    message_sid?: string;
    status: 'received' | 'processed' | 'sent' | 'error';
    created_at: string;
    twilio_data?: any;
}

interface PaginatedLogs {
    data: WebhookLog[];
    current_page: number;
    last_page: number;
    per_page: number;
    total: number;
    next_page_url?: string;
    prev_page_url?: string;
}

interface Props {
    logs: PaginatedLogs;
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Webhook Logs',
        href: '/twilio/logs',
    },
];

// Filters
const searchQuery = ref('');
const selectedType = ref('');
const selectedStatus = ref('');
const selectedLog = ref<WebhookLog | null>(null);
const showDetailsDialog = ref(false);

// Computed properties
const filteredLogs = computed(() => {
    let filtered = props.logs.data;

    if (searchQuery.value) {
        const query = searchQuery.value.toLowerCase();
        filtered = filtered.filter(log =>
            log.from_number?.toLowerCase().includes(query) ||
            log.to_number?.toLowerCase().includes(query) ||
            log.content?.toLowerCase().includes(query)
        );
    }

    if (selectedType.value) {
        filtered = filtered.filter(log => log.type === selectedType.value);
    }

    if (selectedStatus.value) {
        filtered = filtered.filter(log => log.status === selectedStatus.value);
    }

    return filtered;
});

const getStatusIcon = (status: string) => {
    switch (status) {
        case 'received':
            return Clock;
        case 'processed':
        case 'sent':
            return CheckCircle;
        case 'error':
            return XCircle;
        default:
            return AlertCircle;
    }
};

const getStatusColor = (status: string) => {
    switch (status) {
        case 'received':
            return 'secondary';
        case 'processed':
        case 'sent':
            return 'default';
        case 'error':
            return 'destructive';
        default:
            return 'outline';
    }
};

const getTypeIcon = (type: string) => {
    switch (type) {
        case 'voice':
            return Phone;
        case 'sms':
        case 'sms_forward':
            return MessageSquare;
        default:
            return AlertCircle;
    }
};

const formatDate = (dateString: string) => {
    return new Date(dateString).toLocaleString();
};

const formatPhoneNumber = (phoneNumber: string) => {
    if (!phoneNumber) return '';
    // Simple formatting for US numbers
    const cleaned = phoneNumber.replace(/\D/g, '');
    if (cleaned.length === 11 && cleaned.startsWith('1')) {
        return `+1 (${cleaned.slice(1, 4)}) ${cleaned.slice(4, 7)}-${cleaned.slice(7)}`;
    }
    return phoneNumber;
};

const viewDetails = (log: WebhookLog) => {
    selectedLog.value = log;
    showDetailsDialog.value = true;
};

const refreshLogs = () => {
    router.reload();
};

const clearFilters = () => {
    searchQuery.value = '';
    selectedType.value = '';
    selectedStatus.value = '';
};

const goToPage = (page: number) => {
    router.get(route('twilio.logs'), { page }, { preserveState: true });
};
</script>

<template>
    <Head title="Webhook Logs" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-4 rounded-xl p-4">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-2xl font-bold tracking-tight">Webhook Logs</h1>
                    <p class="text-muted-foreground">
                        Monitor incoming calls and SMS messages from Twilio
                    </p>
                </div>
                <div class="flex items-center gap-2">
                    <Button
                        variant="outline"
                        size="sm"
                        @click="refreshLogs"
                        class="gap-2"
                    >
                        <RefreshCw class="h-4 w-4" />
                        Refresh
                    </Button>
                    <Button
                        variant="outline"
                        size="sm"
                        as-child
                        class="gap-2"
                    >
                        <Link :href="route('twilio.settings')">
                            <Settings class="h-4 w-4" />
                            Settings
                        </Link>
                    </Button>
                </div>
            </div>

            <!-- Stats Cards -->
            <div class="grid gap-4 md:grid-cols-4">
                <Card>
                    <CardContent class="p-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-muted-foreground">Total Logs</p>
                                <p class="text-2xl font-bold">{{ props.logs.total }}</p>
                            </div>
                            <div class="rounded-full bg-blue-100 p-3">
                                <AlertCircle class="h-6 w-6 text-blue-600" />
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardContent class="p-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-muted-foreground">Voice Calls</p>
                                <p class="text-2xl font-bold">
                                    {{ props.logs.data.filter(log => log.type === 'voice').length }}
                                </p>
                            </div>
                            <div class="rounded-full bg-green-100 p-3">
                                <Phone class="h-6 w-6 text-green-600" />
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardContent class="p-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-muted-foreground">SMS Messages</p>
                                <p class="text-2xl font-bold">
                                    {{ props.logs.data.filter(log => log.type === 'sms' || log.type === 'sms_forward').length }}
                                </p>
                            </div>
                            <div class="rounded-full bg-purple-100 p-3">
                                <MessageSquare class="h-6 w-6 text-purple-600" />
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardContent class="p-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-sm font-medium text-muted-foreground">Errors</p>
                                <p class="text-2xl font-bold">
                                    {{ props.logs.data.filter(log => log.status === 'error').length }}
                                </p>
                            </div>
                            <div class="rounded-full bg-red-100 p-3">
                                <XCircle class="h-6 w-6 text-red-600" />
                            </div>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Filters -->
            <Card>
                <CardHeader>
                    <CardTitle class="flex items-center gap-2">
                        <Filter class="h-5 w-5" />
                        Filters
                    </CardTitle>
                </CardHeader>
                <CardContent class="space-y-4">
                    <div class="grid gap-4 md:grid-cols-4">
                        <div class="space-y-2">
                            <Label for="search">Search</Label>
                            <div class="relative">
                                <Search class="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                                <Input
                                    id="search"
                                    v-model="searchQuery"
                                    placeholder="Search phone numbers or content..."
                                    class="pl-10"
                                />
                            </div>
                        </div>

                        <div class="space-y-2">
                            <Label for="type">Type</Label>
                            <Select v-model:value="selectedType">
                                <SelectTrigger>
                                    <SelectValue placeholder="All types" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="">All types</SelectItem>
                                    <SelectItem value="voice">Voice</SelectItem>
                                    <SelectItem value="sms">SMS</SelectItem>
                                    <SelectItem value="sms_forward">SMS Forward</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>

                        <div class="space-y-2">
                            <Label for="status">Status</Label>
                            <Select v-model:value="selectedStatus">
                                <SelectTrigger>
                                    <SelectValue placeholder="All statuses" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="">All statuses</SelectItem>
                                    <SelectItem value="received">Received</SelectItem>
                                    <SelectItem value="processed">Processed</SelectItem>
                                    <SelectItem value="sent">Sent</SelectItem>
                                    <SelectItem value="error">Error</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>

                        <div class="flex items-end">
                            <Button
                                variant="outline"
                                @click="clearFilters"
                                class="w-full"
                            >
                                Clear Filters
                            </Button>
                        </div>
                    </div>
                </CardContent>
            </Card>

            <!-- Logs Table -->
            <Card>
                <CardHeader>
                    <CardTitle>Recent Activity</CardTitle>
                    <CardDescription>
                        Latest webhook calls and SMS messages
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="overflow-x-auto">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead class="w-[100px]">Type</TableHead>
                                    <TableHead>From</TableHead>
                                    <TableHead>To</TableHead>
                                    <TableHead>Content</TableHead>
                                    <TableHead>Status</TableHead>
                                    <TableHead>Date</TableHead>
                                    <TableHead class="w-[100px]">Actions</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                <TableRow v-for="log in filteredLogs" :key="log.id">
                                    <TableCell>
                                        <div class="flex items-center gap-2">
                                            <component :is="getTypeIcon(log.type)" class="h-4 w-4" />
                                            <span class="capitalize">{{ log.type.replace('_', ' ') }}</span>
                                        </div>
                                    </TableCell>
                                    <TableCell class="font-mono text-sm">
                                        {{ formatPhoneNumber(log.from_number) }}
                                    </TableCell>
                                    <TableCell class="font-mono text-sm">
                                        {{ formatPhoneNumber(log.to_number) }}
                                    </TableCell>
                                    <TableCell class="max-w-[200px] truncate">
                                        {{ log.content || '-' }}
                                    </TableCell>
                                    <TableCell>
                                        <Badge :variant="getStatusColor(log.status)" class="gap-1">
                                            <component :is="getStatusIcon(log.status)" class="h-3 w-3" />
                                            {{ log.status }}
                                        </Badge>
                                    </TableCell>
                                    <TableCell class="text-sm text-muted-foreground">
                                        {{ formatDate(log.created_at) }}
                                    </TableCell>
                                    <TableCell>
                                        <Button
                                            variant="ghost"
                                            size="sm"
                                            @click="viewDetails(log)"
                                        >
                                            <Eye class="h-4 w-4" />
                                        </Button>
                                    </TableCell>
                                </TableRow>
                                <TableRow v-if="filteredLogs.length === 0">
                                    <TableCell colspan="7" class="text-center py-8 text-muted-foreground">
                                        No logs found matching your criteria
                                    </TableCell>
                                </TableRow>
                            </TableBody>
                        </Table>
                    </div>

                    <!-- Pagination -->
                    <div v-if="props.logs.last_page > 1" class="flex items-center justify-between mt-4">
                        <p class="text-sm text-muted-foreground">
                            Showing {{ ((props.logs.current_page - 1) * props.logs.per_page) + 1 }} to 
                            {{ Math.min(props.logs.current_page * props.logs.per_page, props.logs.total) }} 
                            of {{ props.logs.total }} results
                        </p>
                        <div class="flex items-center gap-2">
                            <Button
                                variant="outline"
                                size="sm"
                                :disabled="props.logs.current_page === 1"
                                @click="goToPage(props.logs.current_page - 1)"
                            >
                                Previous
                            </Button>
                            <span class="text-sm">
                                Page {{ props.logs.current_page }} of {{ props.logs.last_page }}
                            </span>
                            <Button
                                variant="outline"
                                size="sm"
                                :disabled="props.logs.current_page === props.logs.last_page"
                                @click="goToPage(props.logs.current_page + 1)"
                            >
                                Next
                            </Button>
                        </div>
                    </div>
                </CardContent>
            </Card>
        </div>

        <!-- Details Dialog -->
        <Dialog v-model:open="showDetailsDialog">
            <DialogContent class="max-w-2xl">
                <DialogHeader>
                    <DialogTitle class="flex items-center gap-2">
                        <component :is="getTypeIcon(selectedLog?.type || '')" class="h-5 w-5" />
                        Log Details
                    </DialogTitle>
                    <DialogDescription>
                        Detailed information about this webhook call
                    </DialogDescription>
                </DialogHeader>
                <div v-if="selectedLog" class="space-y-4">
                    <div class="grid gap-4 md:grid-cols-2">
                        <div>
                            <Label class="text-sm font-medium">Type</Label>
                            <p class="text-sm capitalize">{{ selectedLog.type.replace('_', ' ') }}</p>
                        </div>
                        <div>
                            <Label class="text-sm font-medium">Status</Label>
                            <Badge :variant="getStatusColor(selectedLog.status)" class="gap-1">
                                <component :is="getStatusIcon(selectedLog.status)" class="h-3 w-3" />
                                {{ selectedLog.status }}
                            </Badge>
                        </div>
                        <div>
                            <Label class="text-sm font-medium">From Number</Label>
                            <p class="text-sm font-mono">{{ formatPhoneNumber(selectedLog.from_number) }}</p>
                        </div>
                        <div>
                            <Label class="text-sm font-medium">To Number</Label>
                            <p class="text-sm font-mono">{{ formatPhoneNumber(selectedLog.to_number) }}</p>
                        </div>
                        <div v-if="selectedLog.call_sid">
                            <Label class="text-sm font-medium">Call SID</Label>
                            <p class="text-sm font-mono">{{ selectedLog.call_sid }}</p>
                        </div>
                        <div v-if="selectedLog.message_sid">
                            <Label class="text-sm font-medium">Message SID</Label>
                            <p class="text-sm font-mono">{{ selectedLog.message_sid }}</p>
                        </div>
                        <div class="md:col-span-2">
                            <Label class="text-sm font-medium">Date</Label>
                            <p class="text-sm">{{ formatDate(selectedLog.created_at) }}</p>
                        </div>
                    </div>

                    <div v-if="selectedLog.content">
                        <Label class="text-sm font-medium">Content</Label>
                        <p class="text-sm p-3 bg-muted rounded-md">{{ selectedLog.content }}</p>
                    </div>

                    <div v-if="selectedLog.twilio_data">
                        <Label class="text-sm font-medium">Twilio Data</Label>
                        <ScrollArea class="h-[200px] w-full border rounded-md p-3">
                            <pre class="text-xs">{{ JSON.stringify(selectedLog.twilio_data, null, 2) }}</pre>
                        </ScrollArea>
                    </div>
                </div>
            </DialogContent>
        </Dialog>
    </AppLayout>
</template>
