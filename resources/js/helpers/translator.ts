
export function trans(key: string): string {
    return (window as any).translations?.[key] ?? key
}
