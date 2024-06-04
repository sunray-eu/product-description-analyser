import { Language, SentimentManager } from 'node-nlp';
import * as process from 'process';

const languageManager = new Language();
const sentimentManager = new SentimentManager();

async function analyzeSentiment(text: string): Promise<number> {
  try {
    const langResult = languageManager.guess(text);
    if (!langResult.length) {
      throw new Error('Language not detected');
    }

    const language = langResult[0].alpha2;
    const sentiment = await sentimentManager.process(language, text);

    if (!sentiment || !('score' in sentiment) || sentiment.score === undefined) {
      throw new Error(`Sentiment analysis failed: ${JSON.stringify(sentiment)}`);
    }

    return sentiment.score;
  } catch (error: any) {
    throw new Error(`Error during analysis: ${error.message}`);
  }
}

// Read input from command line
const input = process.argv[2];

if (!input) {
  console.error('Please provide a text input.');
  process.exit(1);
}

analyzeSentiment(input).then((score) => {
  console.log(score);
}).catch(err => {
  console.error('Error:', err.message);
  process.exit(1);
});
