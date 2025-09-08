import { filterPoints } from './filterHelper.js';
import { elements } from '../dom.js';
export const plus = (data) => {
    elements.filterPlus.addEventListener('click', () => {
        filterPoints('plus', data);
    });
};
export const minus = (data) => {
    elements.filterMinus.addEventListener('click', () => {
        filterPoints('minus', data);
    });
};
export const all = (data) => {
    elements.filterAll.addEventListener('click', () => {
        filterPoints('all', data);
    });
};

