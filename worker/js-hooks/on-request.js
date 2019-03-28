/*
  Hook: page.on('request', request => { ... })
  Function body to execute when puppeteer hook is triggered
  https://pptr.dev/#?product=Puppeteer&version=v1.13.0&show=api-requestcontinueoverrides
*/

const blacklist = '${blacklist}'.split(',');
if (blacklist.find(regex => request.url().match(regex) ) ) {
  return request.abort();
}
request.continue();
