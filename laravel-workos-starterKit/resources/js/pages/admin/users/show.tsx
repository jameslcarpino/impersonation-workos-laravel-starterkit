import { Head, Link } from '@inertiajs/react';
import AppLayout from '@/layouts/app-layout';
import { type BreadcrumbItem } from '@/types';

interface User {
    id: number;
    name: string;
    email: string;
    workos_id: string;
    avatar?: string;
    created_at: string;
    updated_at: string;
}

interface Props {
    user: User;
}

export default function UserShow({ user }: Props) {
    const breadcrumbs: BreadcrumbItem[] = [
        {
            title: 'Admin',
            href: '/admin',
        },
        {
            title: 'Users',
            href: '/admin',
        },
        {
            title: user.name,
            href: `/admin/users/${user.id}`,
        },
    ];

    return (
        <AppLayout breadcrumbs={breadcrumbs}>
            <Head title={`User: ${user.name}`} />
            <div className="flex h-full flex-1 flex-col gap-4 rounded-xl p-4 overflow-x-auto">
                <div className="flex items-center justify-between">
                    <h1 className="text-2xl font-bold">User Details</h1>
                    <Link
                        href="/admin"
                        className="px-4 py-2 bg-gray-600 text-white rounded hover:bg-gray-700 transition-colors"
                    >
                        Back to Admin
                    </Link>
                </div>
                
                <div className="bg-white dark:bg-gray-800 shadow rounded-lg">
                    <div className="px-4 py-5 sm:p-6">
                        <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <h3 className="text-lg leading-6 font-medium text-gray-900 dark:text-gray-100 mb-4">
                                    User Information
                                </h3>
                                <dl className="space-y-4">
                                    <div>
                                        <dt className="text-sm font-medium text-gray-500 dark:text-gray-400">Name</dt>
                                        <dd className="mt-1 text-sm text-gray-900 dark:text-gray-100">{user.name}</dd>
                                    </div>
                                    <div>
                                        <dt className="text-sm font-medium text-gray-500 dark:text-gray-400">Email</dt>
                                        <dd className="mt-1 text-sm text-gray-900 dark:text-gray-100">{user.email}</dd>
                                    </div>
                                    <div>
                                        <dt className="text-sm font-medium text-gray-500 dark:text-gray-400">WorkOS ID</dt>
                                        <dd className="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                            <code className="bg-gray-100 dark:bg-gray-700 px-2 py-1 rounded text-xs">
                                                {user.workos_id}
                                            </code>
                                        </dd>
                                    </div>
                                    <div>
                                        <dt className="text-sm font-medium text-gray-500 dark:text-gray-400">Created</dt>
                                        <dd className="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                            {new Date(user.created_at).toLocaleString()}
                                        </dd>
                                    </div>
                                    <div>
                                        <dt className="text-sm font-medium text-gray-500 dark:text-gray-400">Last Updated</dt>
                                        <dd className="mt-1 text-sm text-gray-900 dark:text-gray-100">
                                            {new Date(user.updated_at).toLocaleString()}
                                        </dd>
                                    </div>
                                </dl>
                            </div>
                            
                            <div>
                                <h3 className="text-lg leading-6 font-medium text-gray-900 dark:text-gray-100 mb-4">
                                    Impersonation
                                </h3>
                                <div className="bg-yellow-50 dark:bg-yellow-900/20 border border-yellow-200 dark:border-yellow-800 rounded-lg p-4">
                                    <p className="text-sm text-yellow-700 dark:text-yellow-300 mb-4">
                                        To impersonate this user, you need to use the WorkOS Dashboard:
                                    </p>
                                    <ol className="text-sm text-yellow-700 dark:text-yellow-300 list-decimal list-inside space-y-2">
                                        <li>Go to your WorkOS Dashboard</li>
                                        <li>Navigate to the Users section</li>
                                        <li>Find this user (WorkOS ID: <code className="text-xs">{user.workos_id}</code>)</li>
                                        <li>Click "Impersonate User" in the user's details</li>
                                        <li>You'll be redirected back to this application as this user</li>
                                    </ol>
                                    
                                    <div className="mt-4 p-3 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded">
                                        <p className="text-sm text-blue-700 dark:text-blue-300">
                                            <strong>Note:</strong> Impersonation sessions automatically expire after 60 minutes.
                                        </p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </AppLayout>
    );
} 