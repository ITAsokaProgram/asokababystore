export function getCookie(name) {
    const value = document.cookie.match('(^|;)\\s*' + name + '\\s*=\\s*([^;]+)');
    if (value) return value[2];
    return null;
}

export default getCookie