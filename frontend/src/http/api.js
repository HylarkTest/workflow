import axios from 'axios';

export const userRequest = {
    async get() {
        try {
            const response = await axios('/user');
            return response.data.data;
        } catch (error) {
            if (+error.response.status === 401) {
                return null;
            }
            return Promise.reject(error);
        }
    },
};

export const teamRequest = {
    async get() {
        const response = await axios('/team');
        return response.data.data;
    },
};

export const mappingsRequest = {
    async get() {
        const response = await axios('/mappings');
        return response.data.data;
    },
};

export const categoriesRequest = {
    async get() {
        const response = await axios('/categories');
        return response.data.data;
    },
};
