function toggleMenu() {
	body = document.getElementById('thepage');
	body.className = body.className == '' ? 'show-menu' : '';
	body.innerHTML += '<!-- weird bug fix -->';
	return false;
}