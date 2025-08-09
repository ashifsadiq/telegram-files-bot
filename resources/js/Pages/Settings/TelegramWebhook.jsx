import { Head, router, useForm } from '@inertiajs/react'
import Input from '../../Components/ui/input'
import axios from 'axios';
import { Trash2 } from 'lucide-react';
import { toast, Toaster } from 'sonner';
const TelegramWebhook = ({ currentWebHookUrl }) => {
    const { data, setData, errors, setError, reset, post, put, patch, del, get, setDefaults } = useForm({
        base_url: '',
        isLoading: false
    })
    const deleteWebhook = async (e) => {
        e.preventDefault();
        setData('isLoading', true)
        try {
            const response = await axios.post(route('api.webhook.delete'), data);
            if (response.status == 200) {
                toast.success(response.data.message)
                router.reload()
            }
        } catch (error) {
            if (error.response?.status === 422) {
                setError(error.response.data.errors);
            } else {
                console.error('Other error:', error);
            }
        } finally {
            setData('isLoading', false)
            reset()
        }
    }
    function checkEnter(event) {
        if (event.key === 'Enter') {
            handleSubmit(event)
        }
    }
    const migrateFreshSeed = async (e) => {
        e.preventDefault();

        if (confirm("After this, all tables will be dropped and the database will be reseeded (also you will be logged out). Continue?")) {
            setData('isLoading', true);
            console.log('User clicked OK');
            post(route('api.migrateFreshSeed'))
            // 🔹 Call your Laravel endpoint here
        } else {
            console.log('User clicked Cancel');
        }
    };
    const handleSubmit = async (e) => {
        e.preventDefault();
        setData('isLoading', true)
        try {
            const response = await axios.post(route('api.webhook.update'), data);

            if (response.status == 200) {
                toast.success(response.data.message)
                router.reload()
            }

        } catch (error) {
            if (error.response?.status === 422) {
                setError(error.response.data.errors);
            } else {
                console.error('Other error:', error);
            }
        } finally {
            setData('isLoading', false)
            reset()
        }
    };
    return (
        <>
            <Head title="Telegram Webhook" />
            <div className="relative flex min-h-screen flex-col items-center justify-center">
                <div className="relative w-full max-w-2xl px-6 lg:max-w-7xl">
                    <div className="w-full m-auto max-w-sm text-wrap p-4 bg-white border border-gray-200 rounded-lg shadow-sm sm:p-6 md:p-8 dark:bg-gray-800 dark:border-gray-700 space-y-5">
                        <h5 className="text-xl font-medium text-gray-900 dark:text-white">Sign in to our platform</h5>
                        {currentWebHookUrl && <div className="text-sm flex-wrap text-wrap font-medium text-gray-500 dark:text-gray-300">
                            Current <a href={currentWebHookUrl} className="text-blue-700 hover:underline dark:text-blue-500">{currentWebHookUrl}</a>
                        </div>}
                        <div className="space-y-6">
                            <Input
                                label='Webhook url'
                                placeholder='Enter the Webhook url'
                                onChangeText={value => setData('base_url', value)}
                                id={'base_url'}
                                value={data.base_url}
                                onKeyDown={checkEnter}
                            />
                            {errors.base_url && <div>{errors.base_url}</div>}
                            <div className="flex space-x-2 items-center">
                                <form onSubmit={deleteWebhook}>
                                    <button className="bg-red-500 rounded-lg px-2.5 py-1.5 text-white"><Trash2 /></button>
                                </form>
                                <form className="space-y-6 w-full" onSubmit={handleSubmit}>
                                    <button type="submit" className="w-full text-white bg-blue-700 hover:bg-blue-800 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-blue-600 dark:hover:bg-blue-700 dark:focus:ring-blue-800">Update</button>
                                </form>
                            </div>
                            <div className='border-t-2' />
                            <form className="space-y-6 w-full" onSubmit={migrateFreshSeed}>
                                <button
                                    type="submit"
                                    className="w-full text-white bg-red-600 hover:bg-red-700 focus:ring-4 focus:outline-none focus:ring-red-300 font-medium rounded-lg text-sm px-5 py-2.5 text-center dark:bg-red-700 dark:hover:bg-red-800 dark:focus:ring-red-900"
                                >
                                    php artisan migrate:fresh --seed
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <Toaster />
        </>
    )
}

export default TelegramWebhook