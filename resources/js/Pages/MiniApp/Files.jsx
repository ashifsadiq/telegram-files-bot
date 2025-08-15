import { Head, Link } from '@inertiajs/react'
import 'https://telegram.org/js/telegram-web-app.js?59'
import React from 'react'
import FlatList from 'flatlist-react';
import { File } from 'lucide-react';
import { cn } from '../../lib/utils';

const Files = ({ chatID, files, parentFolderId }) => {
    const WebApp = window.Telegram.WebApp;
    const formatDate = (isoString) => {
        return new Date(isoString).toLocaleString('en-US', {
            year: 'numeric',
            month: 'short',
            day: '2-digit',
            hour: '2-digit',
            minute: '2-digit',
            hour12: true
        });
    };
    function formatBytes(bytes, precision = 2) {
        const units = ['B', 'KB', 'MB', 'GB', 'TB'];

        if (bytes <= 0) {
            return '0 B';
        }

        const pow = Math.min(
            Math.floor(Math.log(bytes) / Math.log(1024)),
            units.length - 1
        );

        const value = bytes / Math.pow(1024, pow);

        return `${value.toFixed(precision)} ${units[pow]}`;
    }


    const RenderFiles = ({ id, file_name, created_at, file_size, type }) => (
        <tr className="hover:bg-gray-100 dark:hover:bg-gray-700 transition">
            <td className="flex items-center gap-2 py-2 px-5">
                {id}. <File size={20} strokeWidth={0} fill={'rgb(120 209 249)'} />
            </td>
            <td>
                <span className="truncate">{file_name}</span>
            </td>
            <td>{formatDate(created_at)}</td>
            <td>{formatBytes(file_size)}</td>
            <td className="capitalize">{type}</td>
        </tr>
    );

    const goToPage = (url) => {
        if (url) {
            window.location.href = url; // Inertia can also use router.visit(url)
        }
    };
    const { current_page, data, first_page_url, from, last_page, last_page_url, links, next_page_url, path, per_page, prev_page_url, to, total } = files;
    return (
        <>
            <Head title='Files' />
            <div className='relative w-full p-3 md:p-6 min-h-screen space-y-5 bg-gray-200 dark:bg-gray-800'>
                <div className="overflow-x-auto bg-white dark:bg-gray-900 rounded-lg shadow-lg">
                    <table className="w-full text-sm text-left border-separate border-spacing-y-1">
                        {files.data?.length > 0 && (
                            <thead className="bg-gray-100 dark:bg-gray-700 text-gray-700 dark:text-gray-300 uppercase text-xs">
                                <tr>
                                    <th colSpan={2} className="px-4 py-3">Name</th>
                                    <th className="px-4 py-3">Added</th>
                                    <th className="px-4 py-3">Size</th>
                                    <th className="px-4 py-3">Kind</th>
                                </tr>
                            </thead>
                        )}
                        <tbody>
                            <FlatList
                                list={files.data}
                                renderItem={(item) => <RenderFiles {...item} />}
                                renderWhenEmpty={() => (
                                    <tr>
                                        <td colSpan={4} className='text-center py-5 text-gray-500'>
                                            No Files Here!
                                        </td>
                                    </tr>
                                )}
                            />
                        </tbody>
                    </table>
                </div>

                {/* Pagination Controls */}
                {(files.prev_page_url || files.next_page_url) && (
                    <div className="flex justify-center items-center gap-4 mt-5">
                        <Link
                            href={route('telegram.web-app.files', {
                                chatID,
                                parentFolderId,
                                page: current_page - 1 // 👈 add this
                            })}
                            disabled={!files.prev_page_url}
                            className={cn(
                                `px-4 py-2 rounded-lg font-medium transition`,
                                files.prev_page_url
                                    ? 'bg-blue-600 text-white hover:bg-blue-700'
                                    : 'bg-gray-400 cursor-not-allowed text-gray-100'
                            )}
                        >
                            ⬅ Previous
                        </Link>
                        <span className="font-medium">
                            {current_page} of {last_page}
                        </span>
                        <Link
                            href={route('telegram.web-app.files', {
                                chatID,
                                parentFolderId,
                                page: current_page + 1 // 👈 add this
                            }) || '#'}
                            disabled={!files.next_page_url}
                            className={cn(
                                `px-4 py-2 rounded-lg font-medium transition`,
                                files.next_page_url
                                    ? 'bg-blue-600 text-white hover:bg-blue-700'
                                    : 'bg-gray-400 cursor-not-allowed text-gray-100'
                            )}
                        >
                            Next ➡
                        </Link>
                    </div>
                )}
            </div>
        </>
    )
}

export default Files
