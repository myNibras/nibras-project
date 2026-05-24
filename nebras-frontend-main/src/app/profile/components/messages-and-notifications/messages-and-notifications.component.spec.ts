import { ComponentFixture, TestBed } from '@angular/core/testing';

import { MessagesAndNotificationsComponent } from './messages-and-notifications.component';

describe('MessagesAndNotificationsComponent', () => {
  let component: MessagesAndNotificationsComponent;
  let fixture: ComponentFixture<MessagesAndNotificationsComponent>;

  beforeEach(async () => {
    await TestBed.configureTestingModule({
      imports: [MessagesAndNotificationsComponent]
    })
    .compileComponents();

    fixture = TestBed.createComponent(MessagesAndNotificationsComponent);
    component = fixture.componentInstance;
    fixture.detectChanges();
  });

  it('should create', () => {
    expect(component).toBeTruthy();
  });
});
