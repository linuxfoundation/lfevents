const PercyScript = require('@percy/script');
let rooturl = process.argv[2];
let delay = 10000;

PercyScript.run(async (page, percySnapshot) => {
  await page.goto(rooturl);
  await page.waitFor(delay);
  await percySnapshot('homepage');

  await page.goto(rooturl + '2019/11/14/new-website-performance/');
  await page.waitFor(delay);
  await percySnapshot('news');

  await page.goto(rooturl + 'about/community/');
  await page.waitFor(delay);
  await percySnapshot('community');

  await page.goto(rooturl + 'kubecon-cloudnativecon-north-america/');
  await page.waitFor(delay);
  await percySnapshot('kubecon-na');

  await page.goto(rooturl + 'kubecon-cloudnativecon-north-america/program/cfp/');
  await page.waitFor(delay);
  await percySnapshot('kubecon-na-cfp');

});