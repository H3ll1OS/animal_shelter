export function getClientPersonalDetails(user) {
    const fullName = typeof user?.name === 'string' ? user.name.trim() : '';
    const [firstName = '', ...lastNameParts] = fullName.split(/\s+/).filter(Boolean);

    return {
        fullName,
        firstName,
        lastName: lastNameParts.join(' '),
        email: typeof user?.email === 'string' ? user.email : '',
        phone: typeof user?.phone === 'string' ? user.phone : '',
        address: typeof user?.address === 'string' ? user.address : ''
    };
}
