import axios from 'axios';
import router from '@/router.js';

async function supportRequest(request, type) {
    let response;
    try {
        response = await axios[type](request);
    } catch (error) {
        if (error.response.status === 404) {
            router.push({ name: 'support.not-found' });
        } else {
            throw error;
        }
    }
    return response || null;
}

export function createTicket(form) {
    return form.post('/api/support');
}

export async function getCategories() {
    const response = await axios.get('/api/support/categories');
    return response.data.data;
}

export async function getFolders() {
    const response = await axios.get('/api/support/categories');
    return _.first(response.data.data).folders;
}

export async function getFolder(id) {
    const response = await supportRequest(`/api/support/folders/${id}`, 'get');
    return response?.data.data || null;
}

export async function getRecentArticles(topics) {
    const queryString = topics && topics.length ? `&${topics.map((topic) => `topics[]=${topic}`).join('&')}` : '';
    const response = await axios.get(`/api/support?recent=true${queryString}`);
    return response.data.data;
}

export async function getRecommendedArticles() {
    const response = await axios.get('/api/support?recommended=true');
    return response.data.data;
}

export async function getPopularCategories() {
    const response = await axios.get('/api/support/categories?popular=true');
    return response.data.data;
}

export async function searchArticles(searchTerm, topics) {
    const queryString = topics && topics.length ? `&${topics.map((topic) => `topics[]=${topic}`).join('&')}` : '';
    const response = await axios.get(`/api/support?search=${searchTerm}${queryString}`);
    return response.data.data;
}

export async function getArticle(id) {
    const response = await supportRequest(`/api/support/${id}`, 'get');
    return response?.data?.data || null;
}

export async function getPopularTopics() {
    const response = await axios.get('/api/support/topics?popular=true');
    return response.data.data;
}

export async function getTopics() {
    const response = await axios.get('/api/support/topics');
    return response.data.data;
}

export function incrementViewCount(id) {
    supportRequest(`/api/support/${id}/view`, 'put');
}

export function thumbsUp(id) {
    axios.put(`/api/support/${id}/thumbs-up`);
}

export function thumbsDown(id) {
    axios.put(`/api/support/${id}/thumbs-down`);
}
