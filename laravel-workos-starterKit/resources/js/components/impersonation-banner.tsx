import { useState } from 'react';
import { router } from '@inertiajs/react';
import { Icon } from '@/components/icon';
import { User, X } from 'lucide-react';

interface ImpersonationBannerProps {
    impersonation: {
        email: string;
        reason?: string;
    } | null;
}

export function ImpersonationBanner({ impersonation }: ImpersonationBannerProps) {
    const [isVisible, setIsVisible] = useState(true);

    // Debug logging
    console.log('ImpersonationBanner: Props:', {
        impersonation,
        isVisible,
        shouldShow: impersonation && isVisible,
    });

    if (!impersonation || !isVisible) {
        return null;
    }

    const handleStopImpersonation = () => {
        window.location.href = '/logout';
    };

    const handleHideBanner = () => {
        setIsVisible(false);
    };

    return (
        <div className="fixed top-0 left-0 right-0 z-50 bg-yellow-500 text-yellow-900 px-4 py-2 shadow-lg">
            <div className="flex items-center justify-between max-w-7xl mx-auto">
                <div className="flex items-center space-x-2">
                    <Icon iconNode={User} className="w-4 h-4" />
                    <span className="font-medium">
                        You are impersonating a user
                    </span>
                    <span className="text-sm opacity-75">
                        (Impersonated by: {impersonation.email})
                    </span>
                    {impersonation.reason && (
                        <span className="text-sm opacity-75">
                            - Reason: {impersonation.reason}
                        </span>
                    )}
                </div>
                <div className="flex items-center space-x-2">
                    <button
                        onClick={handleStopImpersonation}
                        className="px-3 py-1 bg-yellow-600 text-yellow-100 rounded text-sm font-medium hover:bg-yellow-700 transition-colors"
                    >
                        Stop Impersonation
                    </button>
                    <button
                        onClick={handleHideBanner}
                        className="text-yellow-900 hover:text-yellow-700 transition-colors"
                    >
                        <Icon iconNode={X} className="w-4 h-4" />
                    </button>
                </div>
            </div>
        </div>
    );
} 